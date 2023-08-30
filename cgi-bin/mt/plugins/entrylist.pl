# entrylist.pl - Entry collection/exclusion plugin by Lummox JR

package LummoxJR::entrylist;

use MT::Template::Context;
use MT::Util qw( start_end_day start_end_week start_end_month
	html_text_transform munge_comment archive_file_for
	format_ts offset_time_list first_n_words dirify get_entry
	encode_html encode_js remove_html wday_from_ts days_in
	spam_protect encode_php encode_url decode_html encode_xml );

MT::Template::Context->add_container_tag(EntryList => \&entrylist);
MT::Template::Context->add_tag(EntryListInclude => \&entrylist_onetag);
MT::Template::Context->add_tag(EntryListExclude => \&entrylist_onetag);
MT::Template::Context->add_tag(EntryListIncludeAll => \&entrylist_onetag);
MT::Template::Context->add_tag(EntryListExcludeAll => \&entrylist_onetag);
MT::Template::Context->add_container_tag(EntryListIncludeCategory => \&entrylist_category);
MT::Template::Context->add_container_tag(EntryListExcludeCategory => \&entrylist_category);
MT::Template::Context->add_container_tag(EntryListIncludeAuthor => \&entrylist_author);
MT::Template::Context->add_container_tag(EntryListExcludeAuthor => \&entrylist_author);
MT::Template::Context->add_container_tag(EntryListEntries => \&entrylist_entries);
MT::Template::Context->add_tag(EntryListCount => \&entrylist_count);
MT::Template::Context->add_container_tag(EntryListSection => \&entrylist_headfoot);
MT::Template::Context->add_tag(EntryListSectionText => \&entrylist_headfoot);
MT::Template::Context->add_conditional_tag(EntryListHeader => \&entrylist_headfoot);
MT::Template::Context->add_conditional_tag(EntryListFooter => \&entrylist_headfoot);
MT::Template::Context->add_conditional_tag(EntryListNoHeader => \&entrylist_headfoot);
MT::Template::Context->add_conditional_tag(EntryListNoFooter => \&entrylist_headfoot);
MT::Template::Context->add_conditonal_tag(EntryListEmpty => \&entrylist_empty);

sub entrylist {
	my ($ctx,$args,$cond)=@_;
	my $ts = $ctx->{current_timestamp};
	if(!defined($ts)) {
		my @TS = offset_time_list(time, $ctx->stash('blog_id'));
		$ts = sprintf "%04d%02d%02d%02d%02d%02d", $TS[5]+1900, $TS[4]+1, @TS[3,2,1,0];
		}
	my @entries=();
	my $default=1;
	require MT::Entry;
	if(my $entries = $ctx->stash('entries')) {@entries = @$entries; $default=0;}
	my $res='';
	{
	local $ctx->{__stash}{entrylist}=$args->{trim}?2:1;
	my @ids=map {$_=>1} @$entries;
	local $ctx->{__stash}{entrylist_id}=\@ids;
	local $ctx->{__stash}{entrylist_default}=$default;
	local $ctx->{__stash}{entrylist_include}=[];
	local $ctx->{__stash}{entrylist_exclude}=[];
	local $ctx->{__stash}{entrylist_sections}={};
	my $builder=$ctx->stash('builder');
	defined(my $out = $builder->build($ctx, $ctx->stash('tokens'), $cond)) or
		return $ctx->error( $builder->errstr );
	if($args->{trim}) {
		$out=~s/^\s+$//mg;
		$out=~s/^\s+?//s;
		$out=~s/\s+$//s;
		}
	$res .= $out;
	}
	$res;
}

sub entrylist_narrow {
	my $ctx=shift;
	my $included=$ctx->stash('entrylist_include');
	my $excluded=$ctx->stash('entrylist_exclude');
	my $ids=$ctx->stash('entrylist_id');
	my ($id,%uniq);
	# If only excludes were used, and nothing has been done with them yet,
	# load all entries and then make exclusions.
	if($ctx->stash('entrylist_default')) {
		if(@$included) {$#$ids=-1;}
		elsif(!@$ids) {push @$ids, map{$_->id} MT::Entry->load({'blog_id' => $ctx->stash('blog_id'),
		                                                        status => MT::Entry::RELEASE()});}
		$ctx->stash('entrylist_default',0);
		}
	foreach $id (@$ids) {$uniq{$id}++;}
	foreach $id (@$included) {$uniq{$id}++;}
	foreach $id (@$excluded) {delete $uniq{$id};}
	$ctx->stash('entrylist_include',[]);
	$ctx->stash('entrylist_exclude',[]);
	@$ids=keys %uniq;
}

sub entrylist_onetag {
	my ($ctx,$args,$cond)=@_;
	my $tag=$ctx->stash('tag');
	my $entrylist=$ctx->stash('entrylist') or return
		$ctx->error(MT->translate("[_1] can't be used outside of [_2].",
		  "<MT$tag>", "<MTEntryList>" ));
	my ($entry,$clude);
	if($tag=~m/All$/) {
		(my $entries=$ctx->stash('entries')) or return
			$ctx->error(MT->translate("[_1] can't be used outside of a context with a list of entries.",
			  "<MT$tag>" ));
		my $clude=$ctx->stash(($tag=~m/Exclude/)?'entrylist_exclude':'entrylist_include');
		push @$clude, map {$_->id} @$entries;
		}
	elsif($tag=~m/(In|Ex)clude/) {
		$entry=$ctx->stash('entry') or return
			$ctx->error(MT->translate("[_1] can't be used outside of an entry context.",
			  "<MT$tag>" ));
		my $clude=$ctx->stash(($tag=~m/Exclude/)?'entrylist_exclude':'entrylist_include');
		push @$clude, $entry->id;
		}
	'';
}

sub entrylist_category {
	my ($ctx,$args,$cond)=@_;
	my $tag=$ctx->stash('tag');
	my $entrylist=$ctx->stash('entrylist') or return
		$ctx->error(MT->translate("[_1] can't be used outside of [_2].",
		  "<MT$tag>", "<MTEntryList>" ));
	my $builder=$ctx->stash('builder');
	defined(my $cat_name = $builder->build($ctx, $ctx->stash('tokens'), $cond)) or
		return $ctx->error( $builder->errstr );
	my @cats = split /\s+(?:AND|OR)\s+/, $cat_name;
	my %entries;
	require MT::Placement;
	for my $name (@cats) {
		my $cat = MT::Category->load({ label => $name,
		                               blog_id => $blog_id })
			or return $ctx->error(MT->translate(
				"No such category '[_1]'", $name ));
		my @place = MT::Placement->load({ category_id => $cat->id });
		for my $place (@place) {$entries{$place->entry_id}++;}
		}
	my $is_and = $cat_name =~ /AND/;
	my $count = @cats;
	my @ids = $is_and ? grep { $entries{$_} == $count } keys %entries :
	                    keys %entries;
	@ids = grep{MT::Entry->load($_)->status == MT::Entry::RELEASE()} @ids;
	my $clude=$ctx->stash(($tag=~m/Exclude/)?'entrylist_exclude':'entrylist_include');
	push @$clude, @ids;
	'';
}

sub entrylist_author {
	my ($ctx,$args,$cond)=@_;
	my $tag=$ctx->stash('tag');
	my $entrylist=$ctx->stash('entrylist') or return
		$ctx->error(MT->translate("[_1] can't be used outside of [_2].",
		  "<MT$tag>", "<MTEntryList>" ));
	my $builder=$ctx->stash('builder');
	defined(my $author_name = $builder->build($ctx, $ctx->stash('tokens'), $cond)) or
		return $ctx->error( $builder->errstr );
	require MT::Author;
	my $author = MT::Author->load({ name => $author_name }) or
		return $ctx->error(MT->translate(
			"No such author '[_1]'", $author_name ));
	require MT::Entry;
	my @entries=MT::Entry->load({'blog_id' => $ctx->stash('blog_id'),
	                             'author_id' => $author->id,
	                             status => MT::Entry::RELEASE()});
	my $clude=$ctx->stash(($tag=~m/Exclude/)?'entrylist_exclude':'entrylist_include');
	push @$clude, map {$_->id} @entries;
	'';
}

sub entrylist_entries {
	my ($ctx,$args,$cond)=@_;
	defined (my $trim=$ctx->stash('entrylist')) or return
		$ctx->error(MT->translate("[_1] can't be used outside of [_2].",
		  "<MTEntryListEntries>", "<MTEntryList>" ));
	$trim--;
	entrylist_narrow($ctx);
	my $ids=$ctx->{__stash}{entrylist_id};
	my $res='';
	my $offset=$args->{offset} || 0;
	return $res if($offset>=@$ids);
	my $n=$args->{lastn};
	my @entries=map {MT::Entry->load($_)} @$ids;
	my ($i,$j);
	my $so = $args->{sort_order} || $ctx->stash('blog')->sort_order_posts;
	my $col = $args->{sort_by} || 'created_on';
	if($col eq 'shuffle') {
		$offset=0;
		for($i=@entries;--$i>0;) {
			$j=int rand($i+1);
			next if($i==$j);
			@entries[$i,$j]=@entries[$j,$i];
			}
		}
	else {
		$so='descend' if(!$args->{sort_by} && !$args->{sort_order});
		@entries = $so eq 'ascend' ?
			sort { $a->$col() cmp $b->$col() } @entries :
			sort { $b->$col() cmp $a->$col() } @entries;
		}
	splice(@entries, 0, $offset) if $offset;
	splice(@entries, $n) if($n && $n<@entries);
	my($last_day, $next_day) = ('00000000') x 2;
	$i = 0;
	my $builder=$ctx->stash('builder');
	my $tokens=$ctx->stash('tokens');
	my $sections=$ctx->stash('entrylist_sections');
	my (%headers,%footers,%entrysections);
	for my $e (@entries) {
		local $ctx->{__stash}{entry} = $e;
		local $ctx->{current_timestamp} = $e->created_on;
		my $this_day = substr $e->created_on, 0, 8;
		my $next_day = $this_day;
		my $footer = 0;
		my $ee=$entries[$i+1];
		if (defined $ee) {
			$next_day = substr($entries[$i+1]->created_on, 0, 8);
			$footer = $this_day ne $next_day;
		} else { $footer++ }
		foreach $j (keys %$sections) {
			if(!$i) {
				defined($entrysections{$j} = $builder->build($ctx, $sections->{$j}, $cond))
				  or return $ctx->error( $builder->errstr );
				$headers{$j}=1;
				}
			if(defined $ee) {
				my $oldsection=$entrysections{$j};
				local $ctx->{__stash}{entry} = $ee;
				local $ctx->{current_timestamp} = $ee->created_on;
				defined($entrysections{$j} = $builder->build($ctx, $sections->{$j}, $cond))
				  or return $ctx->error( $builder->errstr );
				$footers{$j}=$oldsection ne $entrysections{$j};
				}
			else {$footers{$j}=1;}
			}
		my $out = $builder->build($ctx, $tokens, {
			%$cond,
			DateHeader => ($this_day ne $last_day),
			DateFooter => $footer,
			EntryIfExtended => $e->text_more ? 1 : 0,
			EntryIfAllowComments => $e->allow_comments,
			EntryIfCommentsOpen => $e->allow_comments eq '1',
			EntryIfAllowPings => $e->allow_pings,
			EntriesHeader => !$i,
			EntriesFooter => !defined $ee,
			EntryListHeaders => \%headers,
			EntryListFooters => \%footers,
			});
		return $ctx->error( $builder->errstr ) unless defined $out;
		if($trim) {
			$out=~s/^\s+$//mg;
			$out=~s/^[\n\r]+//s;
			$out=~s/(\n\r|\r\n|\n){3,}$/$1$1/s;    # leave up to 2 trailing line breaks
			}
		$last_day = $this_day;
		foreach $j (keys %$sections) {$headers{$j}=$footers{$j};}
		$res .= $out;
		$i++;
		}
	$res;
}

sub entrylist_count {
	my ($ctx,$args,$cond)=@_;
	$ctx->stash('entrylist') or return
		$ctx->error(MT->translate("[_1] can't be used outside of [_2].",
		  "<MTEntryListCount>", "<MTEntryList>" ));
	entrylist_narrow($ctx);
	my $ids=$ctx->{__stash}{entrylist_id};
	scalar @$ids;
}

sub entrylist_headfoot {
	my ($ctx,$args,$cond)=@_;
	my $tag=$ctx->stash('tag');
	$ctx->stash('entrylist') or return
		$ctx->error(MT->translate("[_1] can't be used outside of [_2].",
		  "<MT$tag>", "<MTEntryList>" ));
	my $name=$args->{name} or return
		$ctx->error(MT->translate("A [_1] argument is required for [_2].",
		  "name", "<MT$tag>" ));
	if($tag eq 'EntryListSection') {
		my $sections=$ctx->stash('entrylist_sections');
		$sections->{$name}=$ctx->stash('tokens');
		return '';
		}
	elsif($tag eq 'EntryListSectionText') {
		my $sections=$ctx->stash('entrylist_sections');
		defined (my $tokens=$sections->{$name}) or return 
			$ctx->error(MT->translate("[_1] is not a defined section for [_2].",
			  $name, "<MT$tag>" ));
		my $builder=$ctx->stash('builder');
		defined (my $out=$builder->build($ctx, $tokens, $cond))
		  or return $ctx->error( $builder->errstr );
		return $out;
		}
	elsif($tag=~s/No//) {
		my $ers=$cond->{$tag.'s'} or return 0;
		return !$ers->{$name};
		}
	else {
		my $ers=$cond->{$tag.'s'} or return 0;
		return $ers->{$name};
		}
	'';
}

sub entrylist_empty {
	my ($ctx,$args,$cond)=@_;
	$ctx->stash('entrylist') or return 0;
	entrylist_narrow($ctx);
	my $ids=$ctx->{__stash}{entrylist_id};
	!@$ids;
}
1;