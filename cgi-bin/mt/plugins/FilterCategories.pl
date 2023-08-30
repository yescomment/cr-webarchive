
# FilterCategories.pl
# Movable Type plugin tags for filtering a category list according to
# specified inclusion or exclusion criteria
# by Kevin Shay
# http://www.staggernation.com/mtplugins/
# last modified July 12, 2004

package MT::Plugin::FilterCategories;
use strict;
use vars qw( $delim $VERSION );
$VERSION = '1.1';

use MT;
use MT::Template::Context;

eval{ require MT::Plugin };
unless ($@) {
    my $plugin = {
        name => "FilterCategories $VERSION",
        description => 'Restrict a category listing by specifying the categories to include or exclude.',
        doc_link => 'http://www.staggernation.com/mtplugins/FilterCategories'
    }; 
    MT->add_plugin(new MT::Plugin($plugin));
}

MT::Template::Context->add_container_tag('FilterCategories' => sub{&_hdlr_filter_categories;});
MT::Template::Context->add_container_tag('FilteredEntryCategories' => sub{&_hdlr_filtered_entry_categories;});

$delim = '\|';

sub _hdlr_filter_categories {
# handler for MT container tag to be used within <MTCategories> or <MTArchiveList>;
# prints its contents only if the current category matches the "include" or 
# "exclude" filtering arguments
#	include: list of category labels that should be printed
#	exclude: list of category labels that should not be printed
	my ($ctx, $args, $cond) = @_;
	my $cat = $ctx->stash('category') || return $ctx->error('No category context');
 	$args->{'exclude'} || $args->{'include'} || $args->{'exclude_archive_cat'}
		|| return $ctx->error('No categories specified');
 	if ($args->{'include'}) {
 		my %include = map { $_ => 1 } split(/$delim/, $args->{'include'});
 		return '' unless ($include{$cat->label});
 	} elsif ($args->{'exclude'}) {
  		for (split(/$delim/, $args->{'exclude'})) {
 			return '' if ($cat->label eq $_);
 		}
 	}
 		# can't check _hdlr_archive_category as we can in MTFilteredEntryCategories
 		# since here we're within MTCategories
	if (defined($args->{'exclude_archive_cat'})) {
		if ($ctx->{current_archive_type} eq 'Category') {
			return '' if ($cat->label eq $ctx->stash('archive_category')->label);
		}
	}
 	defined(my $text = $ctx->stash('builder')->build($ctx, $ctx->stash('tokens'), $cond))
 			|| return $ctx->error($ctx->errstr);
 	return $text;
}

sub _hdlr_filtered_entry_categories {
# handler for MT container tag to print the current entry's categories, 
# allowing filtering by category label
# this is a copy of MT::Template::Context::_hdlr_entry_categories with some 
# code added to enable the filtering arguments (can't "filter from within" 
# as with MTCategories, because MTEntryCategories won't know what we've
# filtered out and will insert the "glue" argument between categories that
# aren't displaying.)
# args to tag:
# 	glue: string to join categories, same as in MTEntryCategories
#	include: list of category labels that should be printed
#	exclude: list of category labels that should not be printed
#		(n.b. results will be odd if you pass both include and exclude)
#	exclude_archive_cat: useful only within a category archive template,
#		indicates that we should not print a listing for the category
#		whose archive is being generated
	my ($ctx, $args, $cond) = @_;
	my $e = $ctx->stash('entry')
		or return $ctx->_no_entry_error('MTFilteredEntryCategories');
	my $cats = $e->categories;
	return '' unless $cats && @$cats;
 	$args->{'exclude'} || $args->{'include'} || $args->{'exclude_archive_cat'}
		|| return $ctx->error('No categories specified');
		# BEGIN ADDED CODE
	my %exclude = ();
	my %include = ();
	if ($args->{'include'}) {
		%include = map { $_ => 1 } split(/$delim/, $args->{'include'});
	} elsif ($args->{'exclude'}) {
		%exclude = map { $_ => 1 } split(/$delim/, $args->{'exclude'});
	}
	if ($args->{'exclude_archive_cat'}) {
		if (my $arch = $ctx->_hdlr_archive_category) {
			$exclude{$arch} = 1;
		}
	}
		# END ADDED CODE

	my $builder = $ctx->stash('builder');
	my $tokens = $ctx->stash('tokens');
	my @res;
	for my $cat (@$cats) {

			# BEGIN ADDED CODE
		next if ($exclude{$cat->label});
		next if (%include && !$include{$cat->label});
			# END ADDED CODE
			
		local $ctx->{__stash}->{category} = $cat;
		defined(my $out = $builder->build($ctx, $tokens, $cond))
		or return $ctx->error( $builder->errstr );
		push @res, $out;
	}
	my $sep = $args->{glue} || '';
	join $sep, @res;
}

1;
