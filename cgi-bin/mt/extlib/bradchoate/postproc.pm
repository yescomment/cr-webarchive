# ---------------------------------------------------------------------------
# Custom Post-Process Handler
# A Plugin for Moveable Type
#
# Release 1.3 - August 15, 2002
#
# From Brad Choate
# http://www.bradchoate.com/
# ---------------------------------------------------------------------------
# This software is provided as-is.
# You may use it for commercial or personal use.
# If you distribute it, please keep this notice intact.
#
# Copyright (c) 2002 Brad Choate
# ---------------------------------------------------------------------------

package bradchoate::postproc;

use strict;
use vars qw($mt_post_process_handler %process_handlers);

*MT::Template::Context::add_global_filter = \&add_global_filter;

sub add_process_handler {
    add_global_filter(undef, @_);
}

sub add_global_filter {
    my $class = shift;
    my ($name, $code) = @_;
    _set_custom_post_process_handler() if !$mt_post_process_handler;
    $process_handlers{$name} = { code => $code };
}

# Magic that 'overrides' the builtin post-process handler, replacing it
# with our own.
sub _set_custom_post_process_handler {
    require MT::Template::Context;

    $mt_post_process_handler = MT::Template::Context::post_process_handler();
    undef &MT::Template::Context::post_process_handler;
    *MT::Template::Context::post_process_handler = \&custom_post_process_handler;
}

# Here we iterate over our custom handlers and then call the original
# post-process handler to handle all the built-in stuff.
sub custom_post_process_handler {
    sub {
	my($ctx, $args, $str) = @_;
	if ($args) {
	    my %local_args = %$args;
	    foreach my $arg (keys %local_args) {
		if (ref($process_handlers{$arg})) {
		    my $code = $process_handlers{$arg}->{code};
		    $str = $code->($str, $local_args{$arg}, $ctx);
		    # remove this one in case we've overriden an MT
		    # post process handler...
		    delete $local_args{$arg};
		}
	    }
	    $str = $mt_post_process_handler->($ctx, \%local_args, $str);
	}
	$str;
    }
}

1;
