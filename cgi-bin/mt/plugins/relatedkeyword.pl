#!/usr/bin/perl

###
#
#	$Id: relatedkeyword.pl,v 1.1 2002/12/13 23:38:00 rayners Exp $
#
#	MTRelatedEntriesByKeyword, version 0.1 
#	by David Raynes <rayners@rayners.org>
#
###

use strict;
use MT::Template::Context;
use MT::Entry;

MT::Template::Context->add_container_tag (RelatedEntriesByKeyword => \&related_keywords);

sub related_keywords {
  my ($ctx, $args) = @_;
  my $res = '';

  my $blog_id = $ctx->stash ('blog_id');
  my $entry = $ctx->stash ('entry') 
    or return $ctx->error("No entry found.");

  return "" if (!defined $entry->keywords);
  my @ekey = split (/\s+/, $entry->keywords);
  my %keywords;
  foreach my $key (@ekey) {
    $keywords{$key}++;
  }
  
  my $builder = $ctx->stash ('builder');
  my $tokens = $ctx->stash ('tokens');

  my @entries;

  my $entry_iter = MT::Entry->load_iter ({ blog_id => $blog_id,
      status => MT::Entry::RELEASE() });
  
  while (my $e = $entry_iter->()) {
    next if $e->id == $entry->id;
    next if (!defined $e->keywords);
    foreach my $keyword (split (/\s+/, $e->keywords)) {
      if ($keywords{$keyword}) {
	push @entries, $e;
	last;
      }
    }
  }
  
  if (@entries) {
    local $ctx->{__stash}{entries} = \@entries;
    defined (my $out = $builder->build ($ctx, $tokens))
      or return $ctx->error ($builder->errstr);
    $res .= $out;
  } else {
    $res = '';
  }

  $res;
}
