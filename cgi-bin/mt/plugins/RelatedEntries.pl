# Related Entries 2.0
# Related entries plugin for Movable Type
#
# Copyright 2002 Kalsey Consulting Group
# http://kalsey.com/
# Using this software signifies your acceptance of the license
# file that accompanies this software.
#
# Installation and usage instructions can be found at
# http://kalsey.com/blog/2002/07/related_entries_plugin.stm

use MT::Template::Context;
MT::Template::Context->add_container_tag(RelatedEntries => sub { &Related; });

sub Related {
    my($ctx, $args) = @_;
    use MT::Entry;
    use MT::Placement;
    my $method = (length($args->{field}) > 0) ? $args->{field} : "primary_category";
    my $blog_id = $ctx->stash('blog_id');
    defined(my $ctx_entry = $ctx->stash('entry'))
        or return $ctx->error("No entry found. Perhaps you used <MTRelatedEntries> outside your individual archive?");
    my @entries = ();
    my $id = $ctx_entry->id;
    my $lastn = checkLastn($tokens);
    if ($method eq "keywords") { # Related by Keyword
        my $search = $ctx_entry->keywords
            or return '';
        my @searchwords;
        if ($search =~ m/,/g) {
            @searchwords = split(/,/, $search);
        } else {
            @searchwords = split(/\s/, $search);
        }
        my $tokens = $ctx->stash('tokens');
        my $iter = MT::Entry->load_iter({
                               blog_id => $blog_id,
                               status => MT::Entry::RELEASE()
                                },
                                { 'sort' => 'created_on' })
            or return '';
            ENTRIES: while (my $entry = $iter->()) {
                # If this entry is the one we're building, skip it
                next if $entry->id == $id; 
                # Does this entry have keywords?
                my $keywords = $entry->keywords or next;
                #$keywords =~ s/,//g;
                for my $word (@searchwords) {
                     if ($keywords =~ m/$word/i) {
                         push @entries, $entry;
                         # stop iterating if the MTEntries lastn has
                         # been reached.
                         last ENTRIES if scalar(@entries) >= $lastn;
                         # This entry has already matched. We don't need
                         # to check it for further matches.
                         last;
                     }
                }
            }
    } #elsif ($method eq "primary_category") { # Related by category
    my $cat = $ctx_entry->category
        or return '';
    my @entriesNew = MT::Entry->load({
                           blog_id => $blog_id,
                           status => MT::Entry::RELEASE()
                            },
                            {
                               'join' => [
                                   'MT::Placement',
                                   'entry_id',
                                   { category_id => $cat->id },
                                   { unique => 1 } 
                               ]
                            })
        or return '';
    push(@entries, @entriesNew);
    splice(@entries, $lastn);
    my $i = 0;
    for my $e (@entries) {                          # Remove an entry from 
        splice(@entries, $i, 1) if $e->id == $id;   # it's own Related list
        $i++;
    }
#    } else {
#        return $ctx->error("Your field attribute ($method) was not recognized.");
#    }
    my $res = '';
    if (@entries) {
        my $tok = $ctx->stash('tokens');
        my $builder = $ctx->stash('builder');
        local $ctx->{__stash}{entries} = \@entries;
        my $out = $builder->build($ctx, $tok);
        return $ctx->error($builder->errstr) unless defined $out;
        $res .= $out;
    } else {
        $res = '';
    }
    $res;
}

sub checkLastn {
    my $tokens = shift;
    for my $tok (@$tokens) {
        for my $item (@$tok) {
            if ($item =~ /MTEntries\s[^>]*lastn\s*=\s*(["'])(.*?)\1/) {
                return "$1";
            }
        }
    }
    return "25";
}