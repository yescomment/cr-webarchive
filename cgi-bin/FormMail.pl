#!/usr/bin/perl -wT
#
# $Id: FormMail.pl,v 2.22 2003/02/21 13:55:24 nickjc Exp $
#

use strict;
use POSIX qw(locale_h strftime);
use Text::Wrap;              # Er for wrapping :)
use Socket;                  # for the inet_aton()
use CGI qw(:standard);
use vars qw(
  $DEBUGGING $emulate_matts_code $secure
  $allow_empty_ref $max_recipients $mailprog @referers
  @allow_mail_to @recipients %recipient_alias
  @valid_ENV $date_fmt $style $send_confirmation_mail
  $confirmation_text $locale $charset $no_content
  $double_spacing $wrap_text $wrap_style $postmaster
);

# PROGRAM INFORMATION
# -------------------
# FormMail.pl $Revision: 2.22 $
#
# This program is licensed in the same way as Perl
# itself. You are free to choose between the GNU Public
# License <http://www.gnu.org/licenses/gpl.html>  or
# the Artistic License
# <http://www.perl.com/pub/a/language/misc/Artistic.html>
#
# For help on configuration or installation see the
# README file or the POD documentation at the end of
# this file.

# USER CONFIGURATION SECTION
# --------------------------
# Modify these to your own settings. You might have to
# contact your system administrator if you do not run
# your own web server. If the purpose of these
# parameters seems unclear, please see the README file.
#
BEGIN
{
  $DEBUGGING         = 1;
  $emulate_matts_code= 0;
  $secure            = 1;
  $allow_empty_ref   = 1;
  $max_recipients    = 5;
  $mailprog          = '/usr/lib/sendmail -oi -t';
  $postmaster        = '';
  @referers          = qw(cityreliquary.org localhost);
  @allow_mail_to     = qw(cityreliquary.org localhost);
  @recipients        = ();
  %recipient_alias   = ('swap' => 'messages@cityreliquary.org');
  @valid_ENV         = qw(REMOTE_HOST REMOTE_ADDR REMOTE_USER HTTP_USER_AGENT);
  $locale            = '';
  $charset           = 'iso-8859-1';
  $date_fmt          = '%A, %B %d, %Y at %H:%M:%S';
  $style             = '/css/nms.css';
  $no_content        = 0;
  $double_spacing    = 1;
  $wrap_text         = 0;
  $wrap_style        = 1;
  $send_confirmation_mail = 0;
  $confirmation_text = <<'END_OF_CONFIRMATION';
From: you@your.com
Subject: form submission

Thank you for your form submission.

END_OF_CONFIRMATION
#
# USER CONFIGURATION << END >>
# ----------------------------
# (no user serviceable parts beyond here)

  use vars qw($VERSION);
  $VERSION = substr q$Revision: 2.22 $, 10, -1;

  # Merge @allow_mail_to and @recipients into a single list of regexps,
  # automatically adding any recipients in %recipient_alias.
  push @allow_mail_to,
     grep( /@/, split(/\s*,\s*/, join ',', values %recipient_alias) );
  push @recipients, map { /\@/ ? "^\Q$_\E\$" : "\@\Q$_\E\$" } @allow_mail_to;

  $secure = 0 if $emulate_matts_code;

  use vars qw(%valid_ENV);
  @valid_ENV{@valid_ENV} = (1) x @valid_ENV;

  use vars qw($style_element);
  $style_element = $style ?
                   qq%<link rel="stylesheet" type="text/css" href="$style" />%
                   : '';

  if ($mailprog =~ /^SMTP:/i) {
    require IO::Socket;
    import IO::Socket;
  }
}

use vars qw($done_headers $hide_recipient $debug_warnings);
$done_headers   = 0;
$hide_recipient = 0;
$debug_warnings = '';

sub html_header {
    if ($CGI::VERSION >= 2.57) {
        # This is the correct way to set the charset
        print header('-type'=>'text/html', '-charset'=>$charset);
    }
    else {
        # However CGI.pm older than version 2.57 doesn't have the
        # -charset option so we cheat:
        print header('-type' => "text/html; charset=$charset");
    }
}

# We need finer control over what gets to the browser and the CGI::Carp
# set_message() is not available everywhere :(
# This is basically the same as what CGI::Carp does inside but simplified
# for our purposes here.

BEGIN
{
   sub fatalsToBrowser
   {
      my ( $message ) = @_;

      if ( $DEBUGGING )
      {
         $message =~ s/</&lt;/g;
         $message =~ s/>/&gt;/g;
      }
      else
      {
         $message = '';
      }

      my ( $pack, $file ) = caller(0);

      return undef if $file =~ /^\(eval/;

      
      $charset = 'iso-8859-1' unless $charset;

      html_header() unless $done_headers;

      print <<EOERR;
<?xml version="1.0" encoding="$charset"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>Error</title>
  </head>
  <body>
     <h1>Application Error</h1>
     <p>
     An error has occurred in the program
     </p>
     <p>
     $message
     </p>
  </body>
</html>
EOERR
     die @_;
   };

   # Don't stomp on global SIG{__DIE__} if we're sharing an
   # interpreter under Apache::Registry
   unless (exists $ENV{MOD_PERL}) {
     $SIG{__DIE__} = \&fatalsToBrowser;
   }
}
local $SIG{__DIE__} = \&fatalsToBrowser;


# We don't need file uploads or very large POST requests.
# Annoying locution to shut up 'used only once' warning in
# older perl.  Localize these to avoid stomping on other
# scripts that need file uploads under Apache::Registry.

local ($CGI::DISABLE_UPLOADS, $CGI::POST_MAX);
$CGI::DISABLE_UPLOADS = 1;
$CGI::POST_MAX        = 1000000;


# Empty the environment of potentially harmful variables,
# and detaint the path.  We accept anything in the path
# because $ENV{PATH} is trusted for a CGI script, and in
# general we have no way to tell what should be there.

delete @ENV{qw(IFS CDPATH ENV BASH_ENV)};
$ENV{PATH} =~ /(.*)/ and $ENV{PATH} = $1;


use vars qw(%Config %Form $checked_recipient);
%Config = ();
%Form = ();
$checked_recipient = '';

check_url();

eval
{
   setlocale(LC_TIME, $locale) if $locale;
};

my $date = strftime($date_fmt, localtime);

my @Field_Order = parse_form();

check_required();

send_mail($date, [@Field_Order]);

if ( $no_content ) {
   print header(-Status => 204);
}
else {
   return_html($date, [@Field_Order]);
}

sub check_url {
  if ( scalar(@referers) and not check_referer(referer()) ) {
    error('bad_referer');
  }
}

sub check_referer
{
  my $check_referer;
  my ($referer) = @_;

  unless ($referer) {
    return 1 if $allow_empty_ref or !$secure;
  }

  if ($referer && ($referer =~ m!^https?://([^/]*\@)?([\w\-\.]+)!i)) {
    my $refHost;

    $refHost = $2;

    foreach my $test_ref (@referers) {
      if ($refHost =~ m|\Q$test_ref\E$|i) {
        $check_referer = 1;
        last;
      }
      elsif ( $secure && $test_ref =~ /\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/ ) {
        if ( my $ref_host = inet_aton($refHost) ) {
          $ref_host = unpack "l", $ref_host;
          if ( my $test_ref_ip = inet_aton($test_ref) ) {
            $test_ref_ip = unpack "l", $test_ref_ip;
            if ( $test_ref_ip == $ref_host ) {
              $check_referer = 1;
              last;
            }
          }
        }
      }
    }
  } else {
    return 0;
  }

  return $check_referer;
};

sub parse_form {

  my @fields = qw(
                  recipient
                  subject
                  email
                  realname
                  redirect
                  bgcolor
                  background
                  link_color
                  vlink_color
                  text_color
                  alink_color
                  title
                  sort
                  print_config
                  required
                  env_report
                  return_link_title
                  return_link_url
                  print_blank_fields
                  missing_fields_redirect
                 );

  @Config{@fields} = (undef) x @fields; # make it undef rather than empty string

  my @field_order;

  foreach (param()) {
    if (exists $Config{$_}) {
      my $val = strip_nonprintable(param($_));
      next if /redirect$/ and not check_url_valid($val);
      next if /^return_link_url$/ and $secure and not check_url_valid($val);
      $Config{$_} = $val;
      $Form{$_} = $val unless $emulate_matts_code;
    } else {
      my @vals = map {strip_nonprintable($_)} param($_);
      my $key = strip_nonprintable($_);
      $Form{$key} = join ' ', @vals;
      push @field_order, $key;
    }
  }

  foreach (qw(required env_report print_config)) {
    if ($Config{$_}) {
      $Config{$_} =~ s/(\s+|\n)?,(\s+|\n)?/,/g;
      $Config{$_} =~ s/(\s+)?\n+(\s+)?//g;
      $Config{$_} = [split(/,/, $Config{$_})];
    } else {
      $Config{$_} = [];
    }
  }

  $Config{env_report} = [ grep { $valid_ENV{$_} } @{$Config{env_report}} ];

  if (defined $Config{'sort'}) {
    if ($Config{'sort'} eq 'alphabetic') {
      @field_order = sort @field_order;
    } elsif ($Config{'sort'} =~ /^\s*order:\s*(.*)$/s) {
      @field_order = split /\s*,\s*/, $1;
    }
  }

  return @field_order;
}

sub check_required {
  my ($require, @error);

  defined $Config{subject} or $Config{subject} = '';
  defined $Config{recipient} or $Config{recipient} = '';
  $Config{subject}   =~ s/[\r\n]+/ /g;
  $Config{recipient} =~ s/[\r\n]+/ /g;

  if (length $Config{recipient}) {
    my (@valid, @recip);

    foreach (split /\s*,\s*/, $Config{recipient}) {
      if (exists $recipient_alias{$_}) {
        push @recip, split /\s*,\s*/, $recipient_alias{$_};
        $hide_recipient = 1;
      }
      else {
        push @recip, $_;
      }
    }

    foreach (@recip) {
      next unless check_email($_);

      if (check_recipient($_)) {
        push @valid, $_;
      }
    }

    error('no_recipient') unless scalar @valid;
    if ($max_recipients > 0 and not $emulate_matts_code) {
      error('too_many_recipients') if scalar @valid > $max_recipients;
    }
    $checked_recipient = join ',', @valid;

  } else {
    my @allow = grep {/\@/} @allow_mail_to;
    if (scalar @allow > 0 and not $emulate_matts_code) {
      $checked_recipient = $allow[0];
      $hide_recipient = 1;
    } else {
      error('no_recipient')
    }
  }

  if ($secure and (! defined(request_method()) || request_method() ne 'POST')) {
    error('bad_method');
  }

  foreach (@{$Config{required}}) {
    if ($_ eq 'email' && !check_email($Config{$_})) {
      push(@error, $_);
    } elsif (defined($Config{$_})) {
      push(@error, $_) unless length $Config{$_};
    } else {
      push(@error,$_) unless defined $Form{$_} and length $Form{$_};
    }
  }

  error('missing_fields', @error) if @error;
}

sub check_recipient {
  my ($recip) = @_;

  foreach my $r (@recipients) {
    if ( ($recip =~ /(?:$r)$/) or $emulate_matts_code and ($recip =~ /$r/i) ) {
      return(1);
    }
  }

  warn_bad_email($recip, "script not configured to allow this address");
  return(0);
}

sub return_html {
  my ($date, $Field_Order) = @_;

  if ($Config{'redirect'}) {
    print redirect $Config{'redirect'};
  } else {
    html_header();
    $done_headers = 1;

    my $title = escape_html( $Config{'title'} || 'Thank You' );
    my $torecipient = 'to ' . escape_html($Config{'recipient'});
    $torecipient = '' if $hide_recipient;
    my $attr = body_attributes(); # surely this should be done with CSS

    print <<EOHTML;
<?xml version="1.0" encoding="$charset"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
     <title>$title</title>
     $style_element
     <style>
       h1.title {
                   text-align : center;
                }
     </style>
  </head>
  <body $attr>$debug_warnings
    <h1 class="title">$title</h1>
    <p>Below is what you submitted $torecipient on $date</p>
    <p><hr size="1" width="75%" /></p>
EOHTML

    foreach (@$Field_Order) {
      my $val = (defined $Form{$_} ? $Form{$_} : '');
      if ($Config{print_blank_fields} || $val !~ /^\s*$/) {
        print '<p><b>', escape_html($_), ':</b> ',
                        escape_html($val), "</p>\n";
      }
    }

    print qq{<p><hr size="1" width="75%" /></p>\n};

    if ($Config{return_link_url} && $Config{return_link_title}) {
      print "<ul>\n";
      print '<li><a href="', escape_html($Config{return_link_url}),
         '">', escape_html($Config{return_link_title}), "</a>\n";
      print "</li>\n</ul>\n";
    }

    print <<END_HTML_FOOTER;
        <hr size="1" width="75%" />
        <p align="center">
           <font size="-1">
             <a href="http://nms-cgi.sourceforge.net/">FormMail</a>
             &copy; 2001  London Perl Mongers
           </font>
        </p>
        </body>
       </html>
END_HTML_FOOTER
  }
}

sub send_mail {
  my ($date, $Field_Order) = @_;

  my $dashes = '-' x 75;

  my $realname = $Config{realname};
  if (defined $realname) {
    $realname = ' (' . cleanup_realname($realname) . ')';
  } else {
    $realname = $Config{realname} = '';
  }

  my $subject = $Config{subject} || 'WWW Form Submission';
  if ($secure) {
    $subject = substr($subject, 0, 256);
  }

  my $email = $Config{email};
  unless (defined $email and check_email($email)) {
    $email = 'nobody';
  }

  if ("$checked_recipient$email$realname$subject" =~ /\r|\n/) {
    die 'multiline variable in mail header, unsafe to continue';
  }

  my $xheader = '';

  # This is more lenient than that in check_referer() because we
  # want to know how people got this far if they are faking it
  # however it is probably prudent to restrict to the characters
  # valid in a URL - or what ?

  if ( $secure and defined (my $referer = referer()) ) {
    if ( $referer =~ /([\d\w.:@&%\/;?,-]{1,128})/ ) {
       $xheader .= "X-HTTP-Referer: [$1]\n";
    }
  }

  # however if remote_addr() is not pukka then something
  # really bad is going on here.

  if ( $secure and defined (my $addr = remote_addr()) ) {
    $addr =~ /^\[?([\d\.]+)\]?$/ or die "bad remote addr [$addr]";

    $addr = $1;

    # The actual name of the program could be useful if there is
    # more than one FormMail on the machine with different names.

    my ( $realagent ) = $0 =~ m%([\d\w.]+)$%;
    $realagent = defined $realagent ? "($realagent)" : '';
    $xheader .= "X-HTTP-Client: [$addr]\n"
             . "X-Mailer: NMS FormMail.pl $realagent v$VERSION"
             . "[http://nms-cgi.sourceforge.net/]\n";
  }

  if ( $send_confirmation_mail and $email =~ /\@/ ) {
    email_start($postmaster, $email);
    email_data($xheader . "To: $email$realname\n$confirmation_text");
    email_end();
  }

  email_start( $postmaster, split(/\s*,\s*/, $checked_recipient) );

  email_data($xheader . <<EOMAIL);
To: $checked_recipient
From: $email$realname
Subject: $subject

Below is the result of your feedback form.  It was submitted by
$Config{realname} (${\( $Config{email}||'' )}) on $date
$dashes
EOMAIL

  email_data("\n\n") if $double_spacing;
  my $nl = ( $double_spacing ? "\n\n" : "\n" );

  if ($Config{print_config}) {
    foreach (@{$Config{print_config}}) {
      email_data("$_: $Config{$_}$nl") if $Config{$_};
    }
  }

  foreach (@{$Field_Order}) {
    my $val = (defined $Form{$_} ? $Form{$_} : '');
    if ($Config{'print_blank_fields'} || $val !~ /^\s*$/) {
      my $field_name = "$_: ";
      if ( $wrap_text and length("$field_name$val") > 72 ) {
        my $subs_indent = '';
        if ( $wrap_style == 1 ) {
          $subs_indent = ' ' x length($field_name);
        }
        $Text::Wrap::columns = 72;
        my $wraped;
        eval { local $SIG{__DIE__} ; $wraped = wrap($field_name,$subs_indent,$val) };
        email_data( ($@ ? "$field_name$val" : $wraped) . $nl );
      }
      else {
        email_data("$field_name$val$nl");
      }
    }
  }

  email_data("$dashes\n\n");

  foreach (@{$Config{env_report}}) {
    email_data("$_: " . strip_nonprintable($ENV{$_}) . "\n") if $ENV{$_};
  }

  email_end();
}

use vars qw($smtp);
sub email_start {
  my ($sender, @recipients) = @_;

  if ($mailprog =~ /^SMTP:([\w\-\.]+(:\d+)?)$/i) {
    my $mailhost = $1;
    $mailhost .= ':25' unless $mailhost =~ /:/;
    $smtp = IO::Socket::INET->new($mailhost);
    defined $smtp or die "SMTP connect to [$mailhost]: $!";

    my $banner = smtp_response();
    $banner =~ /^2/ or die "bad SMTP banner [$banner] from [$mailhost]";

    my $helohost = ($ENV{SERVER_NAME} =~ /^([\w\-\.]+)$/ ? $1 : '.');
    smtp_command("HELO $helohost");
    smtp_command("MAIL FROM:<$sender>");
    foreach my $r (@recipients) {
      smtp_command("RCPT TO:<$r>");
    }
    smtp_command("DATA", '3');
  }
  else {
    my $command = $mailprog;
    $command .= qq{ -f "$postmaster"} if $postmaster;
    my $result;
    eval { local $SIG{__DIE__};
           $result = open SENDMAIL, "| $command"
         };
    if ($@) {
      die $@ unless $@ =~ /Insecure directory/;
      delete $ENV{PATH};
      $result = open SENDMAIL, "| $command";
    }

    die "Can't open mailprog [$command]\n" unless $result;
  }
}

sub email_data {
  my ($data) = @_;

  if (defined $smtp) {
    $data =~ s#\n#\015\012#g;
    $data =~ s#^\.#..#mg;
    $smtp->print($data) or die "write to SMTP server: $!";
  } else {
    print SENDMAIL $data or die "write to sendmail pipe: $!";
  }
}

sub email_end {
  if (defined $smtp) {
    smtp_command(".");
    smtp_command("QUIT");
    undef $smtp;
  } else {
    close SENDMAIL or die "close sendmail pipe failed, mailprog=[$mailprog]";
  }
}

sub smtp_command {
  my ($cmd, $want) = @_;
  defined $want or $want = '2';

  $smtp->print("$cmd\015\012")
      or die "write [$cmd] to SMTP server: $!";

  my $resp = smtp_response();
  unless (substr($resp, 0, 1) eq $want) {
    die "SMTP command [$cmd] gave response [$resp]";
  }
}

sub smtp_response {
  my $line = smtp_getline();
  my $resp = $line;
  while ($line =~ /^\d\d\d\-/) {
    $line = smtp_getline();
    $resp .= $line;
  }
  return $resp;
}

sub smtp_getline {
  my $line = <$smtp>;
  defined $line or die "read from SMTP server: $!";
  return $line;
}  

sub cleanup_realname {
  my ($realname) = @_;

  return '' unless defined $realname;

  $realname =~ s#\s+# #g;

  if ($secure) {
    # Allow no unusual characters and impose a length limit. We
    # need to allow extended ASCII characters because they can
    # occur in non-English names.
    $realname =~ tr# a-zA-Z0-9_\-,./'\200-\377# #cs;
    $realname = substr $realname, 0, 128;
  } else {
    # Be as generous as possible without opening any known or
    # strongly suspected relaying holes.
    $realname =~ tr#()\\#{}/#;
  }

  return $realname;
}

sub check_email {
  my ($email) = @_;

  return 0 if $email =~ /^\s*$/;

  unless ($email =~ /^(.+)\@([a-z0-9_\.\-\[\]]+)$/is) {
    warn_bad_email($email, "malformed email address");
    return 0;
  }
  my ($user, $host) = ($1, $2);

  if ($host =~ /\.\./) {
    warn_bad_email($email, "hostname $host contains '..'");
    return 0;
  } elsif ($host =~ /^\./) {
    warn_bad_email($email, "hostname $host starts with '.'");
    return 0;
  } elsif ($host =~ /\.$/) {
    warn_bad_email($email, "hostname $host ends with '.'");
    return 0;
  }

  if ($emulate_matts_code and not $secure) {
    # Be as generous as possible without opening any known or strongly
    # suspected relaying holes.
    if ($user =~ /([^a-z0-9_\-\.\#\$\&\'\*\+\/\=\?\^\`\{\|\}\~\200-\377])/i) {
      my $c = sprintf '%s (ASCII 0x%.2X)', $1, unpack('C',$1);
      warn_bad_email($email, "bad character $c");
      return 0;
    } else {
      return 1;
    }
  } else {
    # Only allow reasonable email addresses.

    if ($user =~ /([^a-z0-9_\-\.\*\+\=])/i) {
      my $c = sprintf '%s (ASCII 0x%.2X)', $1, unpack('C',$1);
      warn_bad_email($email, "bad character $c");
      return 0;
    } elsif (length $user > 100) {
      warn_bad_email($email, "username part too long");
      return 0;
    }

    if (length $host > 100) {
      warn_bad_email($email, "hostname too long");
      return 0;
    }
    return 1 if $host =~ /^\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\]$/;
    return 1 if $host =~ /^[a-z0-9\-\.]+$/i;

    warn_bad_email($email, "invalid hostname $host");
    return 0;
  }

  # not reached
  return 0;
}

sub warn_bad_email {
  my ($email, $whybad) = @_;

  $debug_warnings .= <<END if $DEBUGGING;
<p>
<font color="red">Warning:</font>
The email address <tt>${\( escape_html($email) )}</tt> was rejected
for the following reason: ${\( escape_html($whybad) )}
</p>
END
}

# check the validity of a URL.

sub check_url_valid {
  my $url = shift;

  # allow relative URLs with sane values
  return 1 if $url =~ m#^[a-z0-9_\-\.\,\+\/]+$#i;

  $url =~ m< ^ (?:ftp|http|https):// [\w\-\.]+ (?:\:\d+)?
               (?: /  [\w\-.!~*'(|);/\@+\$,%#]*   )?
               (?: \? [\w\-.!~*'(|);/\@&=+\$,%#]* )?
             $
           >x ? 1 : 0;
}

sub strip_nonprintable {
  my $text = shift;
  return '' unless defined $text;
  if ($charset =~ /^iso-8859/i)
  {
    # None of the the iso-8859-* charsets have printable
    # characters between \200 and \241.  See
    # http://czyborra.com/charsets/iso8859.html
    $text=~ tr#\t\n\040-\176\241-\377# #cs;
  }
  elsif ($charset =~ /^utf-8$/i)
  {
    # The bytes 0xFE and 0xFF are illegal in UTF-8, see
    # http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
    $text=~ tr#\t\n\040-\176\200-\375# #cs;
  }
  else
  {
    $text=~ tr#\t\n\040-\176\200-\377# #cs;
  }
  return $text;
}

sub body_attributes {
  my %attrs = (bgcolor     => 'bgcolor',
               background  => 'background',
               link_color  => 'link',
               vlink_color => 'vlink',
               alink_color => 'alink',
               text_color  => 'text');

  my $attr = '';

  foreach (keys %attrs) {
    next unless $Config{$_};
    if (/color$/) {
      next unless $Config{$_} =~ /^(?:#[0-9a-z]{6}|[\w\-]{2,50})$/i;
    } elsif ($_ eq 'background') {
      next unless check_url_valid($Config{$_});
    } else {
      die "no check defined for body attribute [$_]";
    }
    $attr .= qq( $attrs{$_}=") . escape_html($Config{$_}) . '"' if $Config{$_};
  }

  return $attr;
}

sub error {
  my ($error, @error_fields) = @_;
  my ($host, $missing_field, $missing_field_list);

  my ($title, $error_body);

  if ($error eq 'bad_referer') {
    my $referer = referer();
    $referer = '' if ! defined( $referer );
    my $escaped_referer = escape_html($referer);

    if ( $referer =~ m|^https?://([\w\.\-]+)|i) {
       $host = $1;
       $title = 'Bad Referrer - Access Denied';
       $error_body =<<EOBODY;
<p>
  The form attempting to use FormMail resides at <tt>$escaped_referer</tt>,
  which is not allowed to access this program.
</p>
<p>
  If you are attempting to configure FormMail to run with this form,
  you need to add the following to \@referers, explained in detail in the
  README file.
</p>
<p>
  Add <tt>'$host'</tt> to your <tt><b>\@referers</b></tt> array.
</p>
EOBODY
    } elsif (length $referer) {
       $title = 'Malformed Referrer - Access Denied';
       $error_body =<<EOBODY;
<p>
  The referrer value <tt>$escaped_referer</tt> cannot be parsed, so
  it is not possible to check that the referring page is allowed to
  access this program.
</p>
EOBODY
    } else {
       $title = 'Missing Referrer - Access Denied';
       $error_body =<<EOBODY;
<p>
  Your browser did not send a <tt>Referer</tt> header with this
  request, so it is not possible to check that the referring page
  is allowed to access this program.
</p>
EOBODY
    }
 }
 elsif ($error eq 'bad_method') {
   my $ref = referer();
   if (defined $ref and $ref =~ m#^https?://#) {
     $ref = 'at <tt>' . escape_html($ref) . '</tt>';
   } else {
     $ref = 'that you just filled in';
   }
   $title = 'Error: GET request';
   $error_body =<<EOBODY;
<p>
  The form $ref fails to specify the POST method, so it would not
  be correct for this script to take any action in response to
  your request.
</p>
<p>
  If you are attempting to configure this form to run with FormMail,
  you need to set the request method to POST in the opening form tag,
  like this:
  <tt>&lt;form action=&quot;/cgi-bin/FormMail.pl&quot; method=&quot;post&quot;&gt;</tt>
</p>
EOBODY
 } elsif ($error eq 'no_recipient') {

   my $recipient = escape_html($Config{recipient});
   $title = 'Error: Bad or Missing Recipient';
   $error_body =<<EOBODY;
<p>
  There was no recipient or an invalid recipient specified in the
  data sent to FormMail. Please make sure you have filled in the
  <tt>recipient</tt> form field with an e-mail address that has
  been configured in <tt>\@recipients</tt> or <tt>\@allow_mail_to</tt>. 
  More information on filling in <tt>recipient/allow_mail_to</tt> 
  form fields and variables can be found in the README file.
</p>
<hr size="1" />
<p>
 The recipient was: [ $recipient ]
</p>
EOBODY
  }
  elsif ( $error eq 'too_many_recipients' ) {
    $title = 'Error: Too many Recipients';
    $error_body =<<EOBODY;
<p>
  The number of recipients configured in the form exceeds the
  maximum number of recipients configured in the script.  If
  you are attempting to configure FormMail to run with this form
  then you will need to increase the <tt>\$max_recipients</tt>
  configuration setting in the script.
</p>
EOBODY
  }
  elsif ( $error eq 'missing_fields' ) {
     if ( $Config{'missing_fields_redirect'} ) {
        print  redirect($Config{'missing_fields_redirect'});
        exit;
      }
      else {
        my $missing_field_list = join '',
                                 map { '<li>' . escape_html($_) . "</li>\n" }
                                 @error_fields;
        $title = 'Error: Blank Fields';
        $error_body =<<EOBODY;
<p>
    The following fields were left blank in your submission form:
</p>
<div class="c2">
   <ul>
     $missing_field_list
   </ul>
</div>
<p>
    These fields must be filled in before you can successfully
    submit the form.
</p>

<p>
    Please use your back button to return to the form and
    try again.
</p>
EOBODY
     }
  }

  html_header();
  $done_headers = 1;
  print <<END_ERROR_HTML;
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>$title</title>
    $style_element
    <style type="text/css">
    <!--
       body {
              background-color: #FFFFFF;
              color: #000000;
             }
       p.c2 {
              font-size: 80%;
              text-align: center;
            }
       th.c1 {
               text-align: center;
               font-size: 143%;
             }
       p.c3 {font-size: 80%; text-align: center}
       div.c2 {margin-left: 2em}
     -->
    </style>
  </head>
  <body>$debug_warnings
    <table border="0" width="600" bgcolor="#9C9C9C" summary="">
      <tr bgcolor="#9C9C9C">
        <th class="c1">$title</th>
      </tr>
      <tr bgcolor="#CFCFCF">
        <td>
          $error_body
          <hr size="1" />
          <p class="c3">
            <a href="http://nms-cgi.sourceforge.net/">FormMail</a>
            &copy; 2001 London Perl Mongers
          </p>
        </td>
      </tr>
    </table>
  </body>
</html>
END_ERROR_HTML
   exit;
}

use vars qw(%escape_html_map);

BEGIN
{
   %escape_html_map = ( '&' => '&amp;',
                        '<' => '&lt;',
                        '>' => '&gt;',
                        '"' => '&quot;',
                        "'" => '&#39;',
                      );
}

sub escape_html {
  my $str = shift;

  my $chars = join '', keys %escape_html_map;

  if (defined($str))
  {
    $str =~ s/([\Q$chars\E])/$escape_html_map{$1}/g;
  }

  return $str;
}

# No __END__ here because that breaks under Apache::Registry

=head1 COPYRIGHT

FormMail $Revision: 2.22 $
Copyright 2001 London Perl Mongers, All rights reserved

=head1 LICENSE

This script is free software; you are free to redistribute it
and/or modify it under the same terms as Perl itself.

=head1 URL

The most up to date version of this script is available from the nms
script archive at  E<lt>http://nms-cgi.sourceforge.net/E<gt>

=head1 SUMMARY

formmail is a script which allows you to receive the results of an
HTML form submission via an email message.

=head1 FILES

In this distribution, you will find the following files:

=over

=item FormMail.pl

The main Perl script

=item README

This documentation. Instructions on how to install and use
formmail

=item EXAMPLES

Some worked examples of ways to set up formmail

=item ChangeLog

The change history of these files

=item MANIFEST

List of files

=back


=head1 CONFIGURATION

There are a number of variables that you can change in FormMail.pl which
alter the way that the program works.

=over

=item $DEBUGGING

This should be set to 1 whilst you are installing
and testing the script. Once the script is live you
should change it to 0. When set to 1, errors will
be output to the browser. This is a security risk and
should not be used when the script is live.

=item $emulate_matts_code

When this variable is set to a true value (e.g. 1)
formmail will work in exactly the same way as its
counterpart at Matt's Script Archive. If it is set
to a false value (e.g. 0) then more advanced features
are switched on. We do not recommend changing this
variable to 1, as the resulting drop in security
may leave your formmail open to use as a SPAM relay.

=item $secure

When this variable is set to a true value (e.g. 1)
many additional security features are turned on.  We
do not recommend changing this variable to 0, as the
resulting drop in security may leave your formmail
open to use as a SPAM relay.

=item $allow_empty_ref

Some web proxies and office firewalls may strip
certain headers from the HTTP request that is sent
by a browser.  Among these is the HTTP_REFERER that
the program uses as an additional check of the
requests validity - this will cause the program to
fail with a 'bad referer' message even though the
configuration seems fine.  In these cases setting
this variable to 1 will stop the program from
complaining about requests where no referer header
was sent while leaving the rest of the security
features intact.

=item $max_recipients

The maximum number of e-mail addresses that any single
form should be allowed to send copies of the e-mail to.
If none of your forms send e-mail to more than one
recipient, then we recommend that you improve the
security of FormMail by reducing this value to 1.
Setting this variable to 0 removes all limits on the
number of recipients of each e-mail.

=item $mailprog

The system command that the script should invoke to
send an outgoing email. This should be the full path
to a program that will read a message from STDIN and
determine the list of message recipients from the
message headers. Any switches that the program
requires should be provided here. Your hosting
provider or system administrator should be able to
tell you what to set this variable to.

A $mailprog setting that works for many UNIX-like
hosts is:

  $mailprog = '/usr/lib/sendmail -oi -t';

Some other UNIX-like hosts need: 

  $mailprog = '/usr/sbin/sendmail -oi -t';

For hosts that lack a suitable sendmail binary (such
as most Windows systems) we have a Perl script which
does the job of the sendmail binary, in the nms_sendmail
package at E<lt>http://nms-cgi.sourceforge.net/E<gt>.
See the README file in the nms_sendmail package for
instructions.

=item @referers

A list of referring hosts. This should be a list of
the names or IP addresses of all the systems that
will host HTML forms that refer to this formmail
script. Only these hosts will be allowed to use the
formmail script. This is needed to prevent others
from hijacking your formmail script for their own use
by linking to it from their own HTML forms.

=item @allow_mail_to

A list of the email addresses that formmail can send
email to. The elements of this list can be either
simple email addresses (like 'you@your.domain') or
domain names (like 'your.domain'). If it's a domain
name then *any* address at the domain will be allowed.

Example: to allow mail to be sent to 'you@your.domain'
or any address at the host 'mail.your.domain', you
would set:

C<@allow_mail_to = qw(you@your.domain mail.your.domain);>

=item @recipients

A list of Perl regular expression patterns that
determine who the script will allow mail to be sent
to in addition to those set in @allow_mail_to. This is
present only for compatibility with the original
formmail script.  We strongly advise against having
anything in @recipients as it's easy to make a mistake
with the regular expression syntax and turn your
formmail into an open SPAM relay.

There is an implicit $ at the end of the regular
expression, but you need to include the ^ if you want
it anchored at the start.  Note also that since '.' is
a regular expression metacharacter, you'll need to
escape it before using it in domain names.

If that last paragraph makes no sense to you then
please don't put anything in @recipients, stick to
using the less error prone @allow_mail_to.

=item %recipient_alias

A hash for predefining a list of recipients in the script,
and then choosing between them using the recipient form
field, while keeping all the email addresses out of the
HTML so that they don't get collected by address
harvesters and sent junk email.

For example, suppose you have three forms on your site,
and you want each to submit to a different email address
and you want to keep the addresses hidden.  You might
set up C<%recipient_alias> like this:

  %recipient_alias = (
                       '1' => 'one@your.domain',
                       '2' => 'two@your.domain',
                       '3' => 'three@your.domain',
                     );

In the HTML form that should submit to the recipient
'two@your.domain', you would then set the recipient with:

  <input type="hidden" name="recipient" value="2" />

=item $locale

This determines the language that is used in the date - by
default this is blank and the language will probably be
english. The following a list of some possible values,
however it should be stressed that not all of these will
be supported on all systems and also this is not a complete
list:

        Catalan           ca_ES
        Croatian          hr_HR
        Czech             cs_CZ
        Danish            da_DK
        Dutc              nl_NL
        Estonian          et_EE
        Finnish           fi_FI
        French            fr_FR
        Galician          gl_ES
        German            de_DE
        Greek             el_GR
        Hebrew            he_IL
        Hungarian         hu_HU
        Icelandic         is_IS
        Italian           it_IT
        Japanese          ja_JP
        Korean            ko_KR
        Lithuanian        lt_LT
        Norwegian         no_NO
        Polish            pl_PL
        Portuguese        pt_PT
        Romanian          ro_RO
        Russian           ru_RU
        Slovak            sk_SK
        Slovenian         sl_SI
        Spanish           es_ES
        Swedish           sv_SE
        Thai              th_TH
        Turkish           tr_TR

=item $charset

The character set to use for output documents.

=item @valid_ENV

A list of all the environment variables that you want
to be able to include in the email. See L<env_report|/item_env_report>
below.

=item $date_fmt   

The format that the date will be displayed in. This
is a string that contains a number of different 'tags'.
Each tag consists of a % character followed by a letter.
Each tag represents one way of displaying a particular
part of the date or time. Here are some common tags:

 %Y - four digit year (2002)
 %y - two digit year (02)
 %m - month of the year (01 to 12)
 %b - short month name (Jan to Dec)
 %B - long month name (January to December)
 %d - day of the month (01 to 31)
 %a - short day name (Sun to Sat)
 %A - long day name (Sunday to Saturday)
 %H - hour in 24 hour clock (00 to 23)
 %I - hour in 12 hour clock (01 to 12)
 %p - AM or PM
 %M - minutes (00 to 59)
 %S - seconds (00 to 59)
 %Z - the name of the local timezone

=item $style

This is the URL of a CSS stylesheet which will be
used for script generated messages.  This should
probably be the same as the one that you use for all
the other pages.  This should be a local absolute URI
fragment.  Set $style to '0' or the empty string if
you do not want to use style sheets.

=item $send_confirmation_mail

If this flag is set to 1 then an additional email
will be sent to the person who submitted the
form.

B<CAUTION:> with this feature turned on it's
possible for someone to put someone else's email
address in the form and submit it 5000 times,
causing this script to send a flood of email to a
third party.  This third party is likely to blame
you for the email flood attack.

=item $confirmation_text

The header and body of the confirmation email
sent to the person who submits the form, if the
$send_confirmation_mail flag is set. We use a
Perl 'here document' to allow us to configure it
as a single block of text in the script. In the
example below, everything between the lines

  $confirmation_text = <<'END_OF_CONFIRMATION';

and

  END_OF_CONFIRMATION

is treated as part of the email. Everything
before the first blank line is taken as part of
the email header, and everything after the first
blank line is the body of the email.

    $confirmation_text = <<'END_OF_CONFIRMATION';
  From: you@your.com
  Subject: form submission

  Thankyou for your form submission.

  END_OF_CONFIRMATION

=back

=head1 INSTALLATION

Formmail is installed simply by copying the file FormMail.pl into your
cgi-bin directory. If you don't know where your cgi-bin directory is, then
please ask your system administrator.

You may need to rename FormMail.pl to FormMail.cgi. Again, your system
administrator will know if this is the case.

You will probably need to turn on execute permissions to the file. You can
do this by running the command "chmod +x FormMail.pl" from your command
line. If you don't have command line access to your web server then there
will probably be an equivalent function in your file transfer program.

To make use of it, you need to write an HTML form that refers to the
FormMail script. Here's an example which will send mail to the address
'feedback@your.domain' when someone submits the form:

  <form method="post" action="http://your.domain/cgi-bin/FormMail.pl">
    <input type="hidden" name="recipient" value="feedback@your.domain" />
    <input type="text" name="feedback" /><br />
    Please enter your comments<br />
    <input type="submit" />
  </form>

=head1 FORM CONFIGURATION

See how the hidden 'recipient' input in the example above told formmail who
to send the mail to? This is how almost all of formmail's configuration
works. Here's the full list of things you can set with hidden form inputs:

=over

=item recipient  

The email address to which the form submission
should be sent. If you would like it copied to
more than one recipient then you can separate
multiple email addresses with commas, for
example:

 <input type="hidden" name="recipient"
        value="you@your.domain,me@your.domain" />

If you leave the 'recipient' field out of the
form, formmail will send to the first address
listed in the @allow_mail_to configuration
variable (see above).  This allows you to avoid
putting your email address in the form, which
might be desirable if you're concerned about
address harvesters collecting it and sending
you SPAM. This feature is disabled if the
emulate_matts_code configuration variable is
set to 0.

=item subject

The subject line for the email. For example:

 <input type="hidden" name="subject"
        value="From the feedback form" />

=item redirect

If this value is present it should be a URL, and
the user will be redirected there after a
successful form submission.  For example:

 <input type="hidden" name="redirect"
        value="http://www.your.domain/foo.html" />

If you don't specify a redirect URL then instead
of redirecting formmail will generate a success
page telling the user that their submission was
successful.

=item bgcolor

The background color for the success page.

=item background

The URL of the background image for the success
page.

=item text_color

The  text color for the success page.

=item link_color

The link color for the success page.

=item vlink_color

The vlink color for the success page.

=item alink_color

The alink color for the success page.

=item title

The title for the success page.

=item return_link_url

The target URL for a link at the end of the
success page. This is normally used to provide
a link from the success page back to your main
page or back to the page with the form on. For
example:

 <input type="hidden" name="return_link_url"
        value="/home.html" />

=item return_link_title

The label for the return link.  For example:

 <input type="hidden" name="return_link_title"
        value="Back to my home page" />

=item sort

This sets the order in which the submitted form
inputs will appear in the email and on the
success page.  It can be the string 'alphabetic'
for alphabetic order, or the string "order:"
followed by a comma separated list of the input
names, for example:

 <input type="hidden" name="sort"
        value="order:name,email,age,comments">

=item print_config

This is mainly used for debugging, and if set it
causes formmail to include a dump of the
specified configuration settings in the email.

For example:

 <input type="hidden" name="print_config"
        value="title,sort">

... will include whatever values you set for
title' and 'sort' (if any) in the email.

=item required

This is a list of fields that the user must fill
in before they submit the form. If they leave
any of these fields blank then they will be sent
back to the form to try again.  For example:

 <input type="hidden" name="required"
        value="name,comments">

=item missing_fields_redirect

If this is set, it must be a URL, and the user
will be redirected there if any of the fields
listed in 'required' are left blank. Use this if
you want finer control over the the error that
the user sees if they miss out a field.

=item env_report

This is a list of the CGI environment variables
that should be included in the email.  This is
useful for recording things like the IP address
of the user in the email. Any environment
variables that you want to use in 'env_report' in
any of your forms will need to be in the
valid_ENV configuration variable described
above.

=item print_blank_fields

If this is set then fields that the user left
blank will be included in the email.  Normally,
blank fields are suppressed to save space.

=back

As well as all these hidden inputs, there are a couple of non-hidden
inputs which get special treatment:

=over

=item email

If one of the things you're asking the user to fill in is their
email address and you call that input 'email', formmail will use
it as the address part of the sender's email address in the
email.

=item realname

If one of the things you're asking the user to fill in is their
full name and you call that input 'realname', formmail will use
it as the name part of the sender's email address in the email.

=back

=head1 COMMON PROBLEMS

=over

=item confusion over the qw operator

In the configuration section at the top of FormMail, we set
the default list of allowed referers with this line of code:

   @referers = qw(dave.org.uk 209.207.222.64 localhost);

This use of the C<qw()> operator is one way to write lists of
strings in Perl.  Another way is like this:

   @referers = ('dave.org.uk','209.207.222.64','localhost');

We prefer the first version because it allows use to leave out
the quote character, but the second version is perfectly valid
and works exactly the same as the C<qw()> version.  You should
use whichever version you feel most comfortable with.  Neither
is better or worse than the other.

What you must not do is try to mix the two, and end up with
something like:

   @referers = qw('dave.org.uk','209.207.222.64','localhost');

This will not work, and you will see unexpected behavior.  In
the case of C<@referers>, the script will always display a
"bad referer" error page.

=item sendmail switches removed

In the configuration section at the top of FormMail, we set
the default mail program to sendmail with this code:

   $mailprog          = '/usr/lib/sendmail -oi -t';

This is actually two different pieces of information; the
location of the sendmail binary (F</usr/lib/sendmail>) and
the command line switches that must be passed to it in order
for it to read the list of message recipients from the 
message header (C<-oi -t>).

If your hosting provider or system administrator tells you that
sendmail is F</usr/sbin/sendmail> on your system, then you must
change the C<$mailprog> line to:

   $mailprog          = '/usr/sbin/sendmail -oi -t';

and not:

   $mailprog          = '/usr/sbin/sendmail';

=back

=head1 SUPPORT

For support of this script please email:

nms-cgi-support@lists.sourceforge.net

=cut

