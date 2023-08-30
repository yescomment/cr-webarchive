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

package plugins::regex;

use vars qw($VERSION);
$VERSION = 1.61;

use strict;
use MT::Template::Context;

if (MT->VERSION =~ m/^2\.2/) {
    require bradchoate::postproc;
}

MT::Template::Context->add_container_tag(Regex => \&Regex);
MT::Template::Context->add_global_filter(regex => \&regex);
MT::Template::Context->add_container_tag(Grep => \&Grep);
MT::Template::Context->add_container_tag(RegexDefine => \&RegexDefine);
MT::Template::Context->add_container_tag(AddRegex => \&RegexDefine);
MT::Template::Context->add_container_tag(IfMatches => \&IfMatches);
MT::Template::Context->add_container_tag(IfNotMatches => \&IfMatches);

sub regex {
    require bradchoate::regex;
    &bradchoate::regex::regex;
}

sub IfMatches {
    require bradchoate::regex;
    &bradchoate::regex::IfMatches;
}

sub Grep {
    require bradchoate::regex;
    &bradchoate::regex::Grep;
}

sub Regex {
    require bradchoate::regex;
    &bradchoate::regex::Regex;
}

sub RegexDefine {
    require bradchoate::regex;
    &bradchoate::regex::RegexDefine;
}

1;
