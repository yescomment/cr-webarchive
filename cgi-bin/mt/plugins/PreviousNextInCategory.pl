use strict;
use warnings;
use MT::Template::Context;
MT::Template::Context->add_container_tag('EntryPreviousInCategory' => \&_hdlr_entry_previous_in_category);
MT::Template::Context->add_container_tag('EntryNextInCategory' => \&_hdlr_entry_next_in_category);

sub _hdlr_entry_previous_in_category {
    my($ctx, $args, $cond) = @_;
    my $e = $ctx->stash('entry')
        or return $ctx->_no_entry_error('MTEntryPrevious');
    my $cat = $e->category
		    or return '';
    my $prev = $e->previous(1);
    my $res = '';
		while ($prev && !$prev->is_in_category($cat)){
		    $prev = $prev->previous(1);
    }
    if ($prev) {
        my $builder = $ctx->stash('builder');
        local $ctx->{__stash}->{entry} = $prev;
        local $ctx->{current_timestamp} = $prev->created_on;
        my %cond = %$cond;
        $cond{EntryIfAllowComments} = $prev->allow_comments;
        $cond{EntryIfCommentsOpen} = $prev->allow_comments eq '1';
        $cond{EntryIfAllowPings} = $prev->allow_pings;
        $cond{EntryIfExtended} = $prev->text_more ? 1 : 0;
        my $out = $builder->build($ctx, $ctx->stash('tokens'), \%cond);
        return $ctx->error( $builder->errstr ) unless defined $out;
        $res .= $out;
    }
    $res;
}
sub _hdlr_entry_next_in_category {
    my($ctx, $args, $cond) = @_;
    my $e = $ctx->stash('entry')
        or return $ctx->_no_entry_error('MTEntryNext');
    my $cat = $e->category
		    or return '';
    my $next = $e->next(1);
    my $res = '';
		while ($next && !$next->is_in_category($cat)){
		    $next = $next->next(1);
    }

    if ($next) {
        my $builder = $ctx->stash('builder');
        local $ctx->{__stash}->{entry} = $next;
        local $ctx->{current_timestamp} = $next->created_on;
        my %cond = %$cond;
        $cond{EntryIfAllowComments} = $next->allow_comments;
        $cond{EntryIfCommentsOpen} = $next->allow_comments eq '1';
        $cond{EntryIfAllowPings} = $next->allow_pings;
        $cond{EntryIfExtended} = $next->text_more ? 1 : 0;
        my $out = $builder->build($ctx, $ctx->stash('tokens'), \%cond);
        return $ctx->error( $builder->errstr ) unless defined $out;
        $res .= $out;
    }
    $res;
}
