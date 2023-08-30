# ---------------------------------------------------------------------------
# Regex Global Tag Attribute
# A Plugin for Movable Type
#
# Release 1.61
# January 26, 2003
#
# From Brad Choate
# http://www.bradchoate.com/
# ---------------------------------------------------------------------------
# This software is provided as-is.
# You may use it for commercial or personal use.
# If you distribute it, please keep this notice intact.
#
# Copyright (c) 2002-2003 Brad Choate
# ---------------------------------------------------------------------------

package bradchoate::regex;

use strict;
use MT::Util qw(decode_html);

# note: match patterns are stored right in the hash
# replace patterns are stored in the array and their index
# is stored in the hash for named patterns.
use vars qw(%replace_patterns @replace_patterns %regex_cache %match_patterns);

sub Regex {
    my ($ctx, $args, $cond) = @_;
    my $pattern = $args->{pattern};
    my $tokens = $ctx->stash('tokens');
    my $builder = $ctx->stash('builder');
    my $out = $builder->build($ctx, $tokens, $cond);
    return $ctx->error($builder->errstr) unless defined $out;
    regex($out, (defined $pattern ? $pattern : '1'), $ctx, $args->{no_html});
}

sub regex {
    my ($str, $param, $ctx, $no_html) = @_;
    $no_html = 0 if !defined $no_html;
    my @regex;
    if ($param eq '1') {
	@regex = @replace_patterns;
    } elsif ($param =~ m|^s\W|) {
	# simple, single regex pattern. eval and return:
	@regex = ({regex=>$param});
    } elsif ($param =~ m|^m\W|) {
	my ($pattern,$options) = ($param =~ m|^m(.)(.*)\1(.*)$|)[1,3];
	my @regex_idxs = map +($replace_patterns{$_}), grep(/$pattern/, keys %replace_patterns);
	if (@regex_idxs) {
	    @regex = map +($replace_patterns[$_]), sort @regex_idxs;
	}
    } else {
	my @names = split /\b/, $param;
	foreach my $name (@names) {
	    if (defined $replace_patterns{$name}) {
		push @regex, $replace_patterns[$replace_patterns{$name}];
	    }
	}
    }
    $str = '' if !defined $str;
    my $tokens;
    foreach my $pattern (@regex) {
	next unless $pattern;  # skip any emptied (deleted) patterns
	my $cpatt = compile_pattern($pattern->{'regex'});
        if ($no_html || $pattern->{no_html}) {
            $tokens ||= _tokenize($str);
            my $out = '';
            my $reprocess = 0;
            foreach my $token (@$tokens) {
                if ($token->[0] eq 'text') {
                    $token->[1] = $cpatt->($token->[1]);
                    $reprocess ||= $token->[1] =~ m/</;
                }
                $out .= $token->[1];
            }
            $str = $out;
            if ($reprocess) {
                $tokens = _tokenize($str);
            }
        } else {
	    $str = $cpatt->($str) if defined $cpatt;
            $tokens = undef;
        }
    }
    $str;
}

sub _tokenize {
    my ($str) = @_;
    my $pos = 0;
    my $len = length $str;
    my @tokens;
    while ($str =~ m!(<([^>]+)>)!gs) {
        my ($whole_tag, $tag) = ($1, $2);
        my $sec_start = pos $str;
        my $tag_start = $sec_start - length $whole_tag;
        push @tokens, ['text', substr($str, $pos, $tag_start - $pos)] if $pos < $tag_start;
        push @tokens, ['tag', $whole_tag];
        $pos = pos $str;
    }
    push @tokens, ['text', substr($str, $pos, $len - $pos)] if $pos < $len;
    \@tokens;
}

sub IfMatches {
    my ($ctx, $args, $cond) = @_;
    my $res = '';
    my $out;
    my $tokens = $ctx->stash('tokens');
    my $builder = $ctx->stash('builder');
    my $not = $ctx->stash('tag') eq 'IfNotMatches';

    my $expr;
    if (defined $args->{expr}) {
	$expr = $args->{expr};
    } elsif (defined $args->{var}) {
	$expr = q{<MT}.$args->{var}.q{>};
    } else {
	$out = $builder->build($ctx, $tokens);
	return $ctx->error($builder->errstr) unless defined $out;
    }
    if (defined $expr) {
	$out = build_expr($ctx, $expr, $cond);
	return unless defined $out;
    }

    my $matches = 0;
    my $val = $args->{value};
    my $pattern = $args->{pattern};
    if (defined $val) {
	$val = build_expr($ctx, $val, $cond);
	return unless defined $val;
	$matches = $out eq $val;
    } elsif (defined $pattern) {
	if ($pattern !~ m|m\W|) {
	    $pattern = $match_patterns{$pattern}->{'regex'};
	    return $ctx->error('Pattern '.$args->{pattern}.' is not defined') unless defined $pattern;
	}
	my $cpatt = compile_pattern($pattern);
	return $ctx->error("Error compiling pattern: $pattern") unless $cpatt;
	$matches = $cpatt->($out);
    }

    if (($matches && !$not) || ($not && !$matches)) {
	if (defined $expr) {
	    $out = $builder->build($ctx, $tokens, $cond);
	    return $ctx->error($builder->errstr) unless defined $out;
	}
	$res = $out;
    } else {
        if (defined $args->{default}) {
            $res = build_expr($ctx, $args->{default}, $cond);
        }
    }
    $res;
}

sub RegexDefine {
    my ($ctx, $args, $cond) = @_;
    my $t = $ctx->stash('tokens');
    my $pattern = $t->[0]->[1];
    if ($pattern =~ m|^m\W|) {
	# matching pattern
	if ($args->{name}) {
	    $match_patterns{$args->{name}} = {'regex' => $pattern};
	} else {
	    return $ctx->error("A name is required for a match regex");
	}
    } else {
	if ($args->{name}) {
	    if (exists $replace_patterns{$args->{name}}) {
		# update existing pattern
		$replace_patterns[$replace_patterns{$args->{name}}]->{'regex'} = $pattern;
	    } else {
		if ($pattern) {
		    push @replace_patterns, {'regex' => $pattern, no_html => $args->{no_html}};
		    $replace_patterns{$args->{name}} = scalar(@replace_patterns) - 1;
		}
	    }
	} else {
	    push @replace_patterns, {'regex' => $pattern, no_html => $args->{no_html}};
	}
    }
    return '';
}

sub Grep {
    my ($ctx, $args, $cond) = @_;
    my $pattern = $args->{pattern};
    my $glue = $args->{glue};
    $glue = "\n" unless defined $glue;
    my @expr;
    if ($pattern =~ m|^m?\W|) {
        $pattern =~ s/^m?/qr/;
        push @expr, compile_pattern($pattern);
    } else {
        my @names = split /\b/, $pattern;
        foreach my $name (@names) {
            if (exists $match_patterns{$name}) {
                $pattern = $match_patterns{$name}->{'regex'};
                $pattern =~ s/^m?/qr/;
                push @expr, compile_pattern($pattern);
            }
        }
    }

    my $builder = $ctx->stash('builder');
    my $tok = $ctx->stash('tokens');
    my $out = $builder->build($ctx, $tok, $cond);
    return $ctx->error($builder->errstr) unless defined $out;
    my @lines = split /\r?\n/, $out;
    my @result;
    foreach my $line (@lines) {
        foreach my $match (@expr) {
            if ($line =~ $match) {
                push @result, $line;
                last;
            }
        }
    }
    my $res = '';
    if (@result) {
        $res = join $glue, @result;
    } else {
        if (defined $args->{default}) {
            $res = build_expr($ctx, $args->{default}, $cond);
        }
    }
    $res;
}

sub compile_pattern {
    my $pattern = shift;
    return undef unless $pattern;
    if (!$regex_cache{$pattern}) {
	if ($pattern =~ m/^s/) {
	    # search...
	    $regex_cache{$pattern} = eval qq{sub {my \$s = shift; \$s =~ $pattern; \$s}};
	} elsif ($pattern =~ m/^qr/) {
            $regex_cache{$pattern} = eval $pattern;
        } else {
	    # match
	    $regex_cache{$pattern} = eval qq{sub {my \$s = shift; \$s =~ $pattern}};
	}
	warn $@ if $@;
    }
    $regex_cache{$pattern};
}

sub build_expr {
    my ($ctx, $val, $cond) = @_;
    $val = decode_html($val);
    if (($val =~ m/\<MT.*?\>/) ||
	($val =~ s/\[(\/?MT(.*?))\]/\<$1\>/g)) {
	my $builder = $ctx->stash('builder');
	my $tok = $builder->compile($ctx, $val);
	defined($val = $builder->build($ctx, $tok, $cond))
	  or return $ctx->error($builder->errstr);
    }
    return $val;
}

1;
