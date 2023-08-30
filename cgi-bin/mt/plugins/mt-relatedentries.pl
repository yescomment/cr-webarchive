package MT::Plugin::RelatedEntries;
use strict;
use warnings;

use vars qw( $VERSION );
$VERSION = 3.01;

eval { require MT::Plugin };
unless ($@) {
    my $static_path = MT::ConfigMgr->instance->StaticWebPath;
    my $about = {
          name        => 'mt-relatedentries',
          description => 'A plugin for Movable Type that displays entries that '
            . "are related to the current entry.",
          doc_link    => $static_path . '/docs/mtrealtedentries.html',
          version     => $VERSION,
          doc_link    => "$static_path/docs/mtrealtedentries.html",
          author_name => 'Appnel Internet Solutions',
          author_link => 'http://www.appnel.com/',
          plugin_link => 'http://code.appnel.com/mt-relatedentries'
    };
    MT->add_plugin(MT::Plugin->new($about));
}

use MT::Template::Context;

MT::Template::Context->add_container_tag(RelatedEntries => \&related);

sub related {
    my ($ctx, $args) = @_;
    require MT::Entry;
    my $src     = $args->{source} || $args->{field} || 'primary_category';
    my $blog_id = $ctx->stash('blog_id');
    my $entry   = $ctx->stash('entry')
      or return $ctx->error('No entry found. Perhaps you used '
                       . '<MTRelatedEntries> outside your individual archive?');
    my $terms = {blog_id => $blog_id, status => MT::Entry::RELEASE};
    my $a     = {'sort' => 'created_on', direction => 'descend'};
    my $id    = $entry->id;
    my $lastn = $args->{lastn} || 25;
    my @entries;
    if ($src eq 'keywords') {    # related by keyword. (sssllllllllllllllow.)
        my $search = $entry->keywords
          or return '';
        my @searchwords =
          $search =~ m/,/g
          ? split(/\s*,\s*/, $search)
          : split(/\s+/,     $search);
        if ($args->{previous}) {
            $a->{range} = {created_on => 1};
            $terms->{created_on} = [0, $entry->created_on];
        }
        my $iter = MT::Entry->load_iter($terms, $a) or return '';
      ENTRIES: while (my $e = $iter->()) {
            my $keywords = $e->keywords;
            next if ($e->id == $id || !$keywords);
            for my $word (@searchwords) {
                if ($keywords =~ m/\b$word\b/i) {
                    push @entries, $e;
                    last ENTRIES if (scalar @entries >= $lastn);
                    last;
                }
            }
        }
    } elsif ($src eq 'primary_category') {    # related by primary category.
        my $cat = $entry->category or return '';
        require MT::Placement;
        $a->{join} = [
                      'MT::Placement', 'entry_id',
                      {category_id => $cat->id, is_primary => 1},
                      {unique      => 1}
          ],
          $a->{limit} = $lastn + 1;
        if ($args->{previous}) {
            $a->{range} = {created_on => 1};
            $terms->{created_on} = ['00000000000000', $entry->created_on];
        }
        @entries = MT::Entry->load($terms, $a)
          or return $ctx->error(MT::Entry->errstr);
        @entries = grep { $_->id != $id } @entries;
        pop @entries
          if scalar @entries > $lastn;    # in case entry wasn't in the set.
    } else {
        return $ctx->error(" The source($src) was not recognized . ");
    }
    return '' unless @entries;
    my $res     = '';
    my $tokens  = $ctx->stash('tokens');
    my $builder = $ctx->stash('builder');
    foreach (@entries) {
        local $ctx->{__stash}{entry}         = $_;
        local $ctx->{current_timestamp}      = $_->created_on;
        local $ctx->{modification_timestamp} = $_->modified_on;
        my $out = $builder->build($ctx, $tokens)
          or return $ctx->error($builder->errstr);
        $res .= $out;
    }
    $res;
}

1;

__END__

=head1 NAME

mt-relatedentries - a plugin for Movable Type that displays
entries that are related to the current entry.

=head1 SYNOPSIS

 <MTRelatedEntries lastn="3" previous="1">
  <p><a href="<$MTEntryLink$>"><$MTEntryTitle$></a> -
  <i><$MTEntryDate format="%b %d, %Y"$></i></p>
 </MTRelatedEntries>

=head1 DESCRIPTION

Related Entries is a plugin for Movable Type that displays
entries that are related to the current entry.

If you place your entries in categories, you can use this
tag provide your readers with the same sort of "Related
Article" feature that is often found on magazine and news
sites.

Prior to version 3.0, the plugin was copyright 2002 Adam
Kalsey, Kalsey Consulting Group.

=head1 TAGS

=over 4

=item * MTRelatedEntries [lastn="" source="" previous=""]

This tag is a container and (essentially) specialized
version of the MTEntries tag. This tag must be used in the
context of another entry.

This tag has a few optional arguments. I<lastn> controls the
number of realted entries similar to the same named argument
of MTEntries. The default is 25. I<source> must be either
'keywords' or 'primary_category'. The default is
'primary_category'. The last is I<previous> which is boolean
the denotes whether to list related entries previous to the
one in context (1) rather then the most current (0, the
default behavior).

=back

=head2 PERFORMANCE CAVEAT

Using 'keywords' as your source is quite slow and resource
intensive. Your rebuild will be slow, very slow. Before you
implement such a scheme be sure you are willing to make this
tradeoff.

=head1 TO DO

=over

=item * Implement "source" plugins

=back

=head1 INSTALLATION

To install mt-relatedentries.pl, simply place the
F<mt-relatedentries.pl> file into your
F<I<MovableTypeHome>/plugins/> directory. Copy the HTML
documentation file in the docs subdirectory to
F<I<MTStaticPath>/docs directory>.

=head1 LICENSE

The software is released under the Artistic License. The
terms of the Artistic License are described at
http://www.perl.com/language/misc/Artistic.html.

=head1 AUTHOR & COPYRIGHT

Except where otherwise noted, mt-relatedentries is
copyright 2005-2006 Appnel Internet Solutions, Timothy Appnel,
tim@appnel.com.
