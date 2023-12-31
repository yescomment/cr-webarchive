package MIME::Lite;


=head1 NAME

MIME::Lite - low-calorie MIME generator


=head1 SYNOPSIS

    use MIME::Lite;
   
Create a single-part message:

    ### Create a new single-part message, to send a GIF file:
    $msg = MIME::Lite->new(
                 From     =>'me@myhost.com',
                 To       =>'you@yourhost.com',
                 Cc       =>'some@other.com, some@more.com',
                 Subject  =>'Helloooooo, nurse!',
                 Type     =>'image/gif',
                 Encoding =>'base64',
                 Path     =>'hellonurse.gif'
		 );

Create a multipart message (i.e., one with attachments):

    ### Create a new multipart message:
    $msg = MIME::Lite->new( 
                 From    =>'me@myhost.com',
                 To      =>'you@yourhost.com',
                 Cc      =>'some@other.com, some@more.com',
                 Subject =>'A message with 2 parts...',
                 Type    =>'multipart/mixed'
		 );
    
    ### Add parts (each "attach" has same arguments as "new"):
    $msg->attach(Type     =>'TEXT',   
                 Data     =>"Here's the GIF file you wanted"
		 );  
    $msg->attach(Type     =>'image/gif',
                 Path     =>'aaa000123.gif',
                 Filename =>'logo.gif'
		 );


Output a message:

    ### Format as a string:
    $str = $msg->as_string;
    
    ### Print to a filehandle (say, a "sendmail" stream):
    $msg->print(\*SENDMAIL);


Send a message:

    ### Send in the "best" way (the default is to use "sendmail"):
    $msg->send;
      


=head1 DESCRIPTION

In the never-ending quest for great taste with fewer calories,
we proudly present: I<MIME::Lite>.  

MIME::Lite is intended as a simple, standalone module for generating
(not parsing!) MIME messages... specifically, it allows you to
output a simple, decent single- or multi-part message with text or binary
attachments.  It does not require that you have the Mail:: or MIME::
modules installed.

You can specify each message part as either the literal data itself (in
a scalar or array), or as a string which can be given to open() to get
a readable filehandle (e.g., "<filename" or "somecommand|").

You don't need to worry about encoding your message data:
this module will do that for you.  It handles the 5 standard MIME encodings.

If you need more sophisticated behavior, please get the MIME-tools
package instead.  I will be more likely to add stuff to that toolkit
over this one.


=head1 MORE EXAMPLES

=head2 Attach a GIF to a text message

This will create a multipart message exactly as above, but using the 
"attach to singlepart" hack:

    ### Create a new multipart message:
    $msg = MIME::Lite->new(
                 From    =>'me@myhost.com',
                 To      =>'you@yourhost.com',
                 Cc      =>'some@other.com, some@more.com',
                 Subject =>'A message with 2 parts...',
                 Type    =>'TEXT',
                 Data    =>"Here's the GIF file you wanted"
                 );  
    
    ### Attach a part:
    $msg->attach(Type     =>'image/gif',
                 Path     =>'aaa000123.gif',
                 Filename =>'logo.gif'
                 );


=head2 Send an HTML document... with images included!

    $msg = MIME::Lite->new(
                 To      =>'you@yourhost.com',
                 Subject =>'HTML with in-line images!',
                 Type    =>'multipart/related'
                 );
    $msg->attach(Type => 'text/html',
                 Data => qq{ <body>
                             Here's <i>my</i> image: 
                             <img src="cid:myimage.gif"> 
                             </body> }
                 );
    $msg->attach(Type => 'image/gif',
                 Id   => 'myimage.gif',
                 Path => '/path/to/somefile.gif',
                 );
    $msg->send();


=head2 Output a message to a filehandle

    ### Write it to a filehandle:
    $msg->print(\*STDOUT); 
     
    ### Write just the header:
    $msg->print_header(\*STDOUT); 
     
    ### Write just the encoded body:
    $msg->print_body(\*STDOUT); 


=head2 Get a message as a string

    ### Get entire message as a string:
    $str = $msg->as_string;
     
    ### Get just the header:
    $str = $msg->header_as_string;
     
    ### Get just the encoded body:
    $str = $msg->body_as_string;


=head2 Change how messages are sent

    ### Do something like this in your 'main':
    if ($I_DONT_HAVE_SENDMAIL) {
       MIME::Lite->send('smtp', "smtp.myisp.net", Timeout=>60);
    }
     
    ### Now this will do the right thing:
    $msg->send;         ### will now use Net::SMTP as shown above






=head1 FAQ


=head2 How do I prevent "Content" headers from showing up in my mail reader?

Apparently, some people are using mail readers which display the MIME
headers like "Content-disposition", and they want MIME::Lite not
to generate them "because they look ugly".  

Sigh.

Y'know, kids, those headers aren't just there for cosmetic purposes.
They help ensure that the message is I<understood> correctly by mail 
readers.  But okay, you asked for it, you got it... 
here's how you can suppress the standard MIME headers.  
Before you send the message, do this:

	$msg->scrub;

You can scrub() any part of a multipart message independently;
just be aware that it works recursively.  Before you scrub,
note the rules that I follow:

=over 4

=item Content-type

You can safely scrub the "content-type" attribute if, and only if, 
the part is of type "text/plain" with charset "us-ascii". 

=item Content-transfer-encoding

You can safely scrub the "content-transfer-encoding" attribute 
if, and only if, the part uses "7bit", "8bit", or "binary" encoding.
You are far better off doing this if your lines are under 1000 
characters.  Generally, that means you I<can> scrub it for plain
text, and you can I<not> scrub this for images, etc.

=item Content-disposition

You can safely scrub the "content-disposition" attribute 
if you trust the mail reader to do the right thing when it decides
whether to show an attachment inline or as a link.  Be aware
that scrubbing both the content-disposition and the content-type
means that there is no way to "recommend" a filename for the attachment!

B<Note:> there are reports of brain-dead MUAs out there that 
do the wrong thing if you I<provide> the content-disposition.
If your attachments keep showing up inline or vice-versa,
try scrubbing this attribute.

=item Content-length

You can always scrub "content-length" safely.

=back


=head2 How do I give my attachment a [different] recommended filename?

By using the Filename option (which is different from Path!):

	$msg->attach(Type => "image/gif",
	             Path => "/here/is/the/real/file.GIF",
	             Filename => "logo.gif");

You should I<not> put path information in the Filename.



=head1 PUBLIC INTERFACE

=head2 Global configuration

To alter the way the entire module behaves, you have the following
methods/options:

=over 4

=item MIME::Lite->quiet()

This L<classmethod|/quiet> can be used to suppress/unsuppress 
all warnings coming from this module.

=item MIME::Lite->send()

When used as a L<classmethod|/send>, this can be used to specify 
a different default mechanism for sending message.  
The initial default is:

    MIME::Lite->send("sendmail", "/usr/lib/sendmail -t -oi -oem");

However, you should consider the similar but smarter and taint-safe variant:

    MIME::Lite->send("sendmail");

Or, for non-Unix users:

    MIME::Lite->send("smtp");


=item $MIME::Lite::PARANOID

If true, we won't attempt to use MIME::Base64/MIME::QuotedPrint, even
if they're available.
Default is B<false>.

=item $MIME::Lite::AUTO_ENCODE

If true, automatically choose the encoding from the content type.
Default is B<true>.

=item $MIME::Lite::AUTO_CC

If true, automatically send to the Cc/Bcc addresses for send_by_smtp().
Default is B<true>.

=item $MIME::Lite::AUTO_VERIFY

If true, check paths to attachments right before printing, raising an exception
if any path is unreadable.
Default is B<true>.

=back

=cut

require 5.004;     ### for /c modifier in m/\G.../gc modifier

use Carp;
use FileHandle;

use strict;
use vars qw(
            $AUTO_CC 
            $AUTO_ENCODE
            $AUTO_VERIFY            
            $PARANOID 
            $QUIET
            $VANILLA
            $VERSION 
            );



#==============================
#==============================
#
# GLOBALS, EXTERNAL/CONFIGURATION...

### The package version, both in 1.23 style *and* usable by MakeMaker:
$VERSION = substr q$Revision: 2.106 $, 10;

### Don't warn me about dangerous activities:
$QUIET = undef;

### Set this true if you don't want to use MIME::Base64/MIME::QuotedPrint:
$PARANOID = 0;

### Unsupported (for tester use): don't qualify boundary with time/pid:
$VANILLA = 0;

### Automatically choose encoding from content type:
$AUTO_ENCODE = 1;

### Automatically interpret CC/BCC for SMTP:
$AUTO_CC = 1;

### Check paths right before printing:
$AUTO_VERIFY = 1;


#==============================
#==============================
#
# GLOBALS, INTERNAL...

### Our sending facilities:
my $Sender     = "sendmail";
my %SenderArgs = (
    "sendmail" => ["/usr/lib/sendmail -t -oi -oem"],
    "smtp"     => [],
    "sub"      => [],
);

### Boundary counter:
my $BCount = 0;

### Known Mail/MIME fields... these, plus some general forms like 
### "x-*", are recognized by build():
my %KnownField = map {$_=>1} 
qw(
   bcc         cc          comments      date          encrypted 
   from        keywords    message-id    mime-version  organization
   received    references  reply-to      return-path   sender        
   subject     to
   );

### What external packages do we use for encoding?
my @Uses;



#==============================
#==============================
#
# PRIVATE UTILITY FUNCTIONS...

#------------------------------ 
#
# fold STRING
#
# Make STRING safe as a field value.  Remove leading/trailing whitespace,
# and make sure newlines are represented as newline+space

sub fold {
    my $str = shift;
    $str =~ s/^\s*|\s*$//g;    ### trim
    $str =~ s/\n/\n /g;      
    $str;
}

#------------------------------
#
# gen_boundary
#
# Generate a new boundary to use.
# The unsupported $VANILLA is for test purposes only.

sub gen_boundary {
    return ("_----------=_".($VANILLA ? '' : int(time).$$).$BCount++);
}

#------------------------------
#
# known_field FIELDNAME
#
# Is this a recognized Mail/MIME field?

sub known_field {
    my $field = lc(shift);
    $KnownField{$field} or ($field =~ m{^(content|resent|x)-.});
}

#------------------------------
#
# is_mime_field FIELDNAME
#
# Is this a field I manage?

sub is_mime_field {
    $_[0] =~ /^(mime\-|content\-)/i;
}

#------------------------------
#
# extract_addrs STRING
#
# Split STRING into an array of email addresses: somewhat of a KLUDGE.
#
# Unless paranoid, we try to load the real code before supplying our own.

my $ATOM      = '[^ \000-\037()<>@,;:\134"\056\133\135]+';
my $QSTR      = '".*?"';
my $WORD      = '(?:' . $QSTR . '|' . $ATOM . ')';
my $DOMAIN    = '(?:' . $ATOM . '(?:' . '\\.' . $ATOM . ')*' . ')';
my $LOCALPART = '(?:' . $WORD . '(?:' . '\\.' . $WORD . ')*' . ')';
my $ADDR      = '(?:' . $LOCALPART . '@' . $DOMAIN . ')';
my $PHRASE    = '(?:' . $WORD . ')+';
my $SEP       = "(?:^\\s*|\\s*,\\s*)";     ### before elems in a list

sub my_extract_addrs {
    my $str = shift;
    my @addrs;
    $str =~ s/\s/ /g;     ### collapse whitespace

    pos($str) = 0;
    while ($str !~ m{\G\s*\Z}gco) {
	### print STDERR "TACKLING: ".substr($str, pos($str))."\n";
	if    ($str =~ m{\G$SEP$PHRASE\s*<\s*($ADDR)\s*>}gco) {push @addrs,$1}
	elsif ($str =~ m{\G$SEP($ADDR)}gco)                   {push @addrs,$1}
	elsif ($str =~ m{\G$SEP($ATOM)}gco)                   {push @addrs,$1}
	else { 
	    my $problem = substr($str, pos($str));
	    die "can't extract address at <$problem> in <$str>\n";
	}
    }
    return @addrs;
}

if (eval "require Mail::Address") {
    push @Uses, "A$Mail::Address::VERSION";
    eval q{
	sub extract_addrs {
	    return map { $_->format } Mail::Address->parse($_[0]);
	}
    }; ### q
}
else {
    eval q{
        sub extract_addrs {
	    return my_extract_addrs(@_);
	}
    }; ### q
} ### if



#==============================
#==============================
#
# PRIVATE ENCODING FUNCTIONS...

#------------------------------
#
# encode_base64 STRING
#
# Encode the given string using BASE64.
# Unless paranoid, we try to load the real code before supplying our own.

if (!$PARANOID and eval "require MIME::Base64") {
    import MIME::Base64 qw(encode_base64);
    push @Uses, "B$MIME::Base64::VERSION";
}
else {
    eval q{
sub encode_base64 {
    my $res = "";
    my $eol = "\n";

    pos($_[0]) = 0;        ### thanks, Andreas!
    while ($_[0] =~ /(.{1,45})/gs) {
	$res .= substr(pack('u', $1), 1);
	chop($res);
    }
    $res =~ tr|` -_|AA-Za-z0-9+/|;

    ### Fix padding at the end:
    my $padding = (3 - length($_[0]) % 3) % 3;
    $res =~ s/.{$padding}$/'=' x $padding/e if $padding;

    ### Break encoded string into lines of no more than 76 characters each:
    $res =~ s/(.{1,76})/$1$eol/g if (length $eol);
    return $res;
} ### sub
  } ### q
} ### if

#------------------------------
#
# encode_qp STRING
#
# Encode the given string, LINE BY LINE, using QUOTED-PRINTABLE.
# Stolen from MIME::QuotedPrint by Gisle Aas, with a slight bug fix: we
# break lines earlier.  Notice that this seems not to work unless
# encoding line by line.
#
# Unless paranoid, we try to load the real code before supplying our own.

if (!$PARANOID and eval "require MIME::QuotedPrint") {
    import MIME::QuotedPrint qw(encode_qp);
    push @Uses, "Q$MIME::QuotedPrint::VERSION";
}
else {
    eval q{
sub encode_qp {
    my $res = shift;
    local($_);
    $res =~ s/([^ \t\n!-<>-~])/sprintf("=%02X", ord($1))/eg;  ### rule #2,#3
    $res =~ s/([ \t]+)$/
      join('', map { sprintf("=%02X", ord($_)) }
	           split('', $1)
      )/egm;                        ### rule #3 (encode whitespace at eol)

    ### rule #5 (lines shorter than 76 chars, but can't break =XX escapes:
    my $brokenlines = "";
    $brokenlines .= "$1=\n" while $res =~ s/^(.{70}([^=]{2})?)//; ### 70 was 74
    $brokenlines =~ s/=\n$// unless length $res; 
    "$brokenlines$res";
} ### sub
  } ### q
} ### if


#------------------------------
#
# encode_8bit STRING
#
# Encode the given string using 8BIT.
# This breaks long lines into shorter ones.

sub encode_8bit {
    my $str = shift;
    $str =~ s/^(.{990})/$1\n/mg;
    $str;
}

#------------------------------
#
# encode_7bit STRING
#
# Encode the given string using 7BIT.
# This NO LONGER protects people through encoding.

sub encode_7bit {
    my $str = shift;
    $str =~ s/[\x80-\xFF]//g; 
    $str =~ s/^(.{990})/$1\n/mg;
    $str;
}

#==============================
#==============================

=head2 Construction

=over 4

=cut


#------------------------------

=item new [PARAMHASH]

I<Class method, constructor.>
Create a new message object.  

If any arguments are given, they are passed into C<build()>; otherwise,
just the empty object is created.

=cut

sub new {
    my $class = shift;

    ### Create basic object:
    my $self = {
	Attrs   => {},     ### MIME attributes
	Header  => [],     ### explicit message headers
	Parts   => [],     ### array of parts
    };    
    bless $self, $class;

    ### Build, if needed:
    return (@_ ? $self->build(@_) : $self);
}


#------------------------------

=item attach [OBJECT|PARAMHASH]

I<Instance method.>
Add a new part to this message, and return the new part.

You can attach a MIME::Lite OBJECT, or have it create one by specifying
a PARAMHASH that will be automatically given to C<new()>.

One of the possibly-quite-useful hacks thrown into this is the 
"attach-to-singlepart" hack: if you attempt to attach a part (let's
call it "part 1") to a message that doesn't have a content-type
of "multipart" or "message", the following happens:

=over 4

=item *

A new part (call it "part 0") is made.

=item *

The MIME attributes and data (but I<not> the other headers)
are cut from the "self" message, and pasted into "part 0".

=item *

The "self" is turned into a "multipart/mixed" message.

=item *

The new "part 0" is added to the "self", and I<then> "part 1" is added.

=back

One of the nice side-effects is that you can create a text message
and then add zero or more attachments to it, much in the same way
that a user agent like Netscape allows you to do.

=cut

sub attach {
    my $self = shift;

    ### Create new part, if necessary:
    my $part1 = ((@_ == 1) ? shift : ref($self)->new(Top=>0, @_));

    ### Do the "attach-to-singlepart" hack:
    if ($self->attr('content-type') !~ m{^(multipart|message)/}i) {

	### Create part zero:
	my $part0 = ref($self)->new;

	### Cut MIME stuff from self, and paste into part zero: 
	foreach (qw(Attrs Data Path FH)) {
	    $part0->{$_} = $self->{$_}; delete($self->{$_});
	}
	$part0->top_level(0);    ### clear top-level attributes

	### Make self a top-level multipart:
	$self->{Attrs} ||= {};   ### reset       
	$self->attr('content-type'              => 'multipart/mixed');
	$self->attr('content-type.boundary'     => gen_boundary());
	$self->attr('content-transfer-encoding' => '7bit');
	$self->top_level(1);     ### activate top-level attributes

	### Add part 0:
	push @{$self->{Parts}}, $part0;
    }

    ### Add the new part:
    push @{$self->{Parts}}, $part1;
    $part1;
}

#------------------------------

=item build [PARAMHASH]

I<Class/instance method, initializer.>
Create (or initialize) a MIME message object.  
Normally, you'll use the following keys in PARAMHASH:

   * Data, FH, or Path      (either one of these, or none if multipart)
   * Type                   (e.g., "image/jpeg")
   * From, To, and Subject  (if this is the "top level" of a message)

The PARAMHASH can contain the following keys:

=over 4

=item (fieldname)

Any field you want placed in the message header, taken from the
standard list of header fields (you don't need to worry about case):

    Bcc           Encrypted     Received      Sender         
    Cc            From          References    Subject 
    Comments	  Keywords      Reply-To      To 
    Content-*	  Message-ID    Resent-*      X-*
    Date          MIME-Version  Return-Path   
                  Organization

To give experienced users some veto power, these fields will be set 
I<after> the ones I set... so be careful: I<don't set any MIME fields>
(like C<Content-type>) unless you know what you're doing!

To specify a fieldname that's I<not> in the above list, even one that's
identical to an option below, just give it with a trailing C<":">,
like C<"My-field:">.  When in doubt, that I<always> signals a mail 
field (and it sort of looks like one too).

=item Data

I<Alternative to "Path" or "FH".>
The actual message data.  This may be a scalar or a ref to an array of
strings; if the latter, the message consists of a simple concatenation 
of all the strings in the array.

=item Datestamp

I<Optional.>
If given true (or omitted), we force the creation of a C<Date:> field 
stamped with the current date/time if this is a top-level message.  
You may want this if using L<send_by_smtp()|/send_by_smtp>.  
If you don't want this to be done, either provide your own Date
or explicitly set this to false.

=item Disposition

I<Optional.>
The content disposition, C<"inline"> or C<"attachment">.
The default is C<"inline">.

=item Encoding

I<Optional.>
The content transfer encoding that should be used to encode your data:

   Use encoding:     | If your message contains:
   ------------------------------------------------------------
   7bit              | Only 7-bit text, all lines <1000 characters
   8bit              | 8-bit text, all lines <1000 characters
   quoted-printable  | 8-bit text or long lines (more reliable than "8bit")
   base64            | Largely non-textual data: a GIF, a tar file, etc.

The default is taken from the Type; generally it is "binary" (no
encoding) for text/*, message/*, and multipart/*, and "base64" for
everything else.  A value of C<"binary"> is generally I<not> suitable
for sending anything but ASCII text files with lines under 1000
characters, so consider using one of the other values instead.

In the case of "7bit"/"8bit", long lines are automatically chopped to
legal length; in the case of "7bit", all 8-bit characters are
automatically I<removed>.  This may not be what you want, so pick your
encoding well!  For more info, see L<"A MIME PRIMER">.

=item FH

I<Alternative to "Data" or "Path".>
Filehandle containing the data, opened for reading.
See "ReadNow" also.

=item Filename

I<Optional.>
The name of the attachment.  You can use this to supply a 
recommended filename for the end-user who is saving the attachment 
to disk, if the one in the Path is inadequate, or if you're using 
the Data argument.  You should I<not> put path information in here
(e.g., no "/" or "\" or ":" characters).

=item Id

I<Optional.>
Same as setting "content-id".

=item Length

I<Optional.>
Set the content length explicitly.  Normally, this header is automatically
computed, but only under certain circumstances (see L<"Limitations">).

=item Path

I<Alternative to "Data" or "FH".>
Path to a file containing the data... actually, it can be any open()able
expression.  If it looks like a path, the last element will automatically 
be treated as the filename. 
See "ReadNow" also.

=item ReadNow

I<Optional, for use with "Path".>
If true, will open the path and slurp the contents into core now.
This is useful if the Path points to a command and you don't want 
to run the command over and over if outputting the message several
times.  B<Fatal exception> raised if the open fails.

=item Top

I<Optional.>
If defined, indicates whether or not this is a "top-level" MIME message.
The parts of a multipart message are I<not> top-level.
Default is true.

=item Type

I<Optional.>
The MIME content type, or one of these special values (case-sensitive):

     "TEXT"   means "text/plain"
     "BINARY" means "application/octet-stream"

The default is C<"TEXT">.

=back

A picture being worth 1000 words (which
is of course 2000 bytes, so it's probably more of an "icon" than a "picture",
but I digress...), here are some examples:

    $msg = MIME::Lite->build( 
               From     => 'yelling@inter.com',
               To       => 'stocking@fish.net',
               Subject  => "Hi there!",
               Type     => 'TEXT',
               Encoding => '7bit',
               Data     => "Just a quick note to say hi!");
 
    $msg = MIME::Lite->build(
               From     => 'dorothy@emerald-city.oz',
               To       => 'gesundheit@edu.edu.edu',
               Subject  => "A gif for U"
               Type     => 'image/gif',
               Path     => "/home/httpd/logo.gif");
 
    $msg = MIME::Lite->build( 
               From     => 'laughing@all.of.us',
               To       => 'scarlett@fiddle.dee.de',
               Subject  => "A gzipp'ed tar file",
               Type     => 'x-gzip',
               Path     => "gzip < /usr/inc/somefile.tar |",
               ReadNow  => 1,
               Filename => "somefile.tgz");

To show you what's really going on, that last example could also 
have been written:

    $msg = new MIME::Lite;
    $msg->build(Type     => 'x-gzip',
                Path     => "gzip < /usr/inc/somefile.tar |",
                ReadNow  => 1,
                Filename => "somefile.tgz");    
    $msg->add(From    => "laughing@all.of.us");
    $msg->add(To      => "scarlett@fiddle.dee.de");
    $msg->add(Subject => "A gzipp'ed tar file");  

=cut

sub build {
    my $self = shift;
    my %params = @_;
    my @params = @_;
    my $key;

    ### Miko's note: reorganized to check for exactly one of Data, Path, or FH
    (defined($params{Data})+defined($params{Path})+defined($params{FH}) <= 1)
	or croak "supply exactly zero or one of (Data|Path|FH).\n";

    ### Create new instance, if necessary:
    ref($self) or $self = $self->new;


    ### CONTENT-TYPE....
    ###

    ### Get content-type:
    my $type = ($params{Type} || 'TEXT');
    ($type eq 'TEXT')   and $type = 'text/plain';
    ($type eq 'BINARY') and $type = 'application/octet-stream';
    $type = lc($type);
    $self->attr('content-type' => $type);
   
    ### Get some basic attributes from the content type:
    my $is_multipart = ($type =~ m{^(multipart)/}i);

    ### Add in the multipart boundary:
    if ($is_multipart) {
	my $boundary = gen_boundary();
	$self->attr('content-type.boundary' => $boundary);
    }


    ### CONTENT-ID...
    ###
    $self->attr('content-id' => $params{Id}) if defined($params{Id});


    ### DATA OR PATH...
    ###    Note that we must do this *after* we get the content type, 
    ###    in case read_now() is invoked, since it needs the binmode().

    ### Get data, as...
    ### ...either literal data:
    if (defined($params{Data})) {
	$self->data($params{Data});
    }
    ### ...or a path to data:
    elsif (defined($params{Path})) {
	$self->path($params{Path});       ### also sets filename
	$self->read_now if $params{ReadNow};
    }
    ### ...or a filehandle to data:
    ### Miko's note: this part works much like the path routine just above,
    elsif (defined($params{FH})) {
	$self->fh($params{FH});
	$self->read_now if $params{ReadNow};  ### implement later
    }
    

    ### FILENAME... (added by Ian Smith <ian@safeway.dircon.co.uk> on 8/4/97)
    ###    Need this to make sure the filename is added.  The Filename
    ###    attribute is ignored, otherwise.
    if (defined($params{Filename})) {
	$self->filename($params{Filename});
    }
  

    ### CONTENT-TRANSFER-ENCODING...
    ###

    ### Get it:
    my $enc = ($params{Encoding} ||  
	       ($AUTO_ENCODE and $self->suggest_encoding($type)) ||
	       'binary');      
    $self->attr('content-transfer-encoding' => lc($enc));
	
    ### Sanity check:
    if ($type =~ m{^(multipart|message)/}) {
	($enc =~ m{^(7bit|8bit|binary)\Z}) or 
	    croak "illegal MIME: can't have encoding $enc with type $type\n";
    }

    ### CONTENT-DISPOSITION...
    ###    Default is inline for single, none for multis:
    ###
    my $disp = ($params{Disposition} or ($is_multipart ? undef : 'inline'));
    $self->attr('content-disposition' => $disp);

    ### CONTENT-LENGTH...
    ###
    my $length;
    if (exists($params{Length})) {   ### given by caller:
	$self->attr('content-length' => $params{Length});
    }
    else {                           ### compute it ourselves
	$self->get_length;
    }

    ### Init the top-level fields:
    my $is_top = defined($params{Top}) ? $params{Top} : 1;
    $self->top_level($is_top);

    ### Datestamp if desired:
    my $ds_wanted    = $params{Datestamp};
    my $ds_defaulted = ($is_top and !exists($params{Datestamp}));
    if (($ds_wanted or $ds_defaulted) and !exists($params{Date})) {
	my ($u_wdy, $u_mon, $u_mdy, $u_time, $u_y4) = 
	    split /\s+/, gmtime()."";   ### should be non-locale-dependent
	my $date = "$u_wdy, $u_mdy $u_mon $u_y4 $u_time UT";
	$self->add("date", $date);
    }
    
    ### Set message headers:
    my @paramz = @params;
    my $field;
    while (@paramz) {
	my ($tag, $value) = (shift(@paramz), shift(@paramz));

	### Get tag, if a tag:
	if ($tag =~ /^-(.*)/) {      ### old style, backwards-compatibility
	    $field = lc($1);
	}
	elsif ($tag =~ /^(.*):$/) {  ### new style
	    $field = lc($1);
	}
	elsif (known_field($field = lc($tag))) {   ### known field
	    ### no-op
	}
	else {                       ### not a field:
	    next;
	}
	
	### Add it:
	$self->add($field, $value);
    }

    ### Done!
    $self;
}

=back

=cut


#==============================
#==============================

=head2 Setting/getting headers and attributes

=over 4

=cut

#------------------------------
#
# top_level ONOFF
#
# Set/unset the top-level attributes and headers.
# This affects "MIME-Version" and "X-Mailer".

sub top_level {
    my ($self, $onoff) = @_;	
    if ($onoff) {
	$self->attr('MIME-Version' => '1.0');
	my $uses = (@Uses ? ("(" . join("; ", @Uses) . ")") : '');
	$self->replace('X-Mailer' => "MIME::Lite $VERSION $uses")
	    unless $VANILLA;
    }
    else {
	$self->attr('MIME-Version' => undef);
	$self->delete('X-Mailer');
    }
}

#------------------------------

=item add TAG,VALUE

Add field TAG with the given VALUE to the end of the header. 
The TAG will be converted to all-lowercase, and the VALUE 
will be made "safe" (returns will be given a trailing space).

B<Beware:> any MIME fields you "add" will override any MIME
attributes I have when it comes time to output those fields.
Normally, you will use this method to add I<non-MIME> fields:

    $msg->add("Subject" => "Hi there!");

Giving VALUE as an arrayref will cause all those values to be added.
This is only useful for special multiple-valued fields like "Received":

    $msg->add("Received" => ["here", "there", "everywhere"]

Giving VALUE as the empty string adds an invisible placeholder
to the header, which can be used to suppress the output of 
the "Content-*" fields or the special  "MIME-Version" field.
When suppressing fields, you should use replace() instead of add():

    $msg->replace("Content-disposition" => "");

I<Note:> add() is probably going to be more efficient than C<replace()>,
so you're better off using it for most applications if you are 
certain that you don't need to delete() the field first.

I<Note:> the name comes from Mail::Header.

=cut

sub add {
    my $self = shift;
    my $tag = lc(shift);
    my $value = shift;

    ### If a dangerous option, warn them:
    carp "Explicitly setting a MIME header field ($tag) is dangerous:\n".
	 "use the attr() method instead.\n"
	if (is_mime_field($tag) && !$QUIET);

    ### Get array of clean values:
    my @vals = ref($value) ? @{$value} : ($value);
    map { s/\n/\n /g } @vals;

    ### Add them:
    foreach (@vals) {
	push @{$self->{Header}}, [$tag, $_];
    }
}

#------------------------------

=item attr ATTR,[VALUE]

Set MIME attribute ATTR to the string VALUE.  
ATTR is converted to all-lowercase.
This method is normally used to set/get MIME attributes:

    $msg->attr("content-type"         => "text/html");
    $msg->attr("content-type.charset" => "US-ASCII");
    $msg->attr("content-type.name"    => "homepage.html");

This would cause the final output to look something like this:

    Content-type: text/html; charset=US-ASCII; name="homepage.html"

Note that the special empty sub-field tag indicates the anonymous 
first sub-field.

Giving VALUE as undefined will cause the contents of the named
subfield to be deleted.

Supplying no VALUE argument just returns the attribute's value:

    $type = $msg->attr("content-type");        ### returns "text/html"
    $name = $msg->attr("content-type.name");   ### returns "homepage.html"

=cut

sub attr {
    my ($self, $attr, $value) = @_;
    $attr = lc($attr);

    ### Break attribute name up:
    my ($tag, $subtag) = split /\./, $attr;
    defined($subtag) or $subtag = '';

    ### Set or get?
    if (@_ > 2) {   ### set:
	$self->{Attrs}{$tag} ||= {};            ### force hash
	delete $self->{Attrs}{$tag}{$subtag};   ### delete first
	if (defined($value)) {                  ### set...
	    $value =~ s/[\r\n]//g;                   ### make clean
	    $self->{Attrs}{$tag}{$subtag} = $value;
	}
    }
	
    ### Return current value:
    $self->{Attrs}{$tag}{$subtag};
}

sub _safe_attr {
    my ($self, $attr) = @_;
    my $v = $self->attr($attr);
    defined($v) ? $v : '';
}

#------------------------------

=item delete TAG

Delete field TAG with the given VALUE to the end of the header.  
The TAG will be converted to all-lowercase.

    $msg->delete("Subject");

I<Note:> the name comes from Mail::Header.

=cut

sub delete {
    my $self = shift;
    my $tag = lc(shift);

    ### Delete from the header:
    my $hdr = [];
    my $field;
    foreach $field (@{$self->{Header}}) {
	push @$hdr, $field if ($field->[0] ne $tag);
    }
    $self->{Header} = $hdr;
    $self;
}

#------------------------------

=item fields

Return the full header for the object, as a ref to an array
of C<[TAG, VALUE]> pairs, where each TAG is all-lowercase.  
Note that any fields the user has explicitly set will override the 
corresponding MIME fields that we would otherwise generate.  
So, don't say...

    $msg->set("Content-type" => "text/html; charset=US-ASCII");

unless you want the above value to override the "Content-type"
MIME field that we would normally generate.

I<Note:> I called this "fields" because the header() method of
Mail::Header returns something different, but similar enough to 
be confusing.

=cut

sub fields {
    my $self = shift;
    my @fields;
    
    ### Get a lookup-hash of all *explicitly-given* fields:
    my %explicit = map { $_->[0] => 1 } @{$self->{Header}};
    
    ### Start with any MIME attributes not given explicitly:
    my $tag;
    foreach $tag (sort keys %{$self->{Attrs}}) {	

	### Skip if explicit:
	next if ($explicit{$tag});         

	### Skip if no subtags:
	my @subtags = keys %{$self->{Attrs}{$tag}}; 
	@subtags or next;

	### Create string:
	my $value;
	defined($value = $self->{Attrs}{$tag}{''}) or next;  ### need default 
	foreach (sort @subtags) {
	    next if ($_ eq '');
	    $value .= qq{; $_="$self->{Attrs}{$tag}{$_}"};
	}
	
	### Add to running fields;
	push @fields, [$tag, $value];
    }
    
    ### Add remaining fields (note that we duplicate the array for safety):
    foreach (@{$self->{Header}}) {
	push @fields, [@{$_}];
    }

    ### Done!
    return \@fields;
}

#------------------------------

=item filename [FILENAME]

Set the filename which this data will be reported as.
This actually sets both "standard" attributes.

With no argument, returns the filename as dictated by the 
content-disposition.

=cut

sub filename {
    my ($self, $filename) = @_;
    if (@_ > 1) {
	$self->attr('content-type.name' => $filename);
	$self->attr('content-disposition.filename' => $filename);
    }
    $self->attr('content-disposition.filename');
}

#------------------------------

=item get TAG,[INDEX]

Get the contents of field TAG, which might have been set 
with set() or replace().  Returns the text of the field.

    $ml->get('Subject', 0);

If the optional 0-based INDEX is given, then we return the INDEX'th
occurence of field TAG.  Otherwise, we look at the context:
In a scalar context, only the first (0th) occurence of the 
field is returned; in an array context, I<all> occurences are returned.  

I<Warning:> this should only be used with non-MIME fields.
Behavior with MIME fields is TBD, and will raise an exception for now.

=cut

sub get {
    my ($self, $tag, $index) = @_;
    $tag = lc($tag); 
    croak "get: can't be used with MIME fields\n" if is_mime_field($tag);
    
    my @all = map { ($_->[0] eq $tag) ? $_->[1] : ()} @{$self->{Header}};
    (defined($index) ? $all[$index] : (wantarray ? @all : $all[0]));
}

#------------------------------

=item get_length

Recompute the content length for the message I<if the process is trivial>, 
setting the "content-length" attribute as a side-effect:

    $msg->get_length;

Returns the length, or undefined if not set.

I<Note:> the content length can be difficult to compute, since it 
involves assembling the entire encoded body and taking the length
of it (which, in the case of multipart messages, means freezing
all the sub-parts, etc.).  

This method only sets the content length to a defined value if the
message is a singlepart with C<"binary"> encoding, I<and> the body is
available either in-core or as a simple file.  Otherwise, the content
length is set to the undefined value.

Since content-length is not a standard MIME field anyway (that's right, kids:
it's not in the MIME RFCs, it's an HTTP thing), this seems pretty fair.

=cut

#----
# Miko's note: I wasn't quite sure how to handle this, so I waited to hear 
# what you think.  Given that the content-length isn't always required, 
# and given the performance cost of calculating it from a file handle,
# I thought it might make more sense to add some some sort of computelength 
# property. If computelength is false, then the length simply isn't 
# computed.  What do you think?
#
# Eryq's reply:  I agree; for now, we can silently leave out the content-type.

sub get_length {
    my $self = shift;

    my $is_multipart = ($self->attr('content-type') =~ m{^multipart/}i);
    my $enc = lc($self->attr('content-transfer-encoding') || 'binary');
    my $length;
    if (!$is_multipart && ($enc eq "binary")){  ### might figure it out cheap:
	if    (defined($self->{Data})) {               ### it's in core
	    $length = length($self->{Data});
	}
	elsif (defined($self->{FH})) {                 ### it's in a filehandle
	    ### no-op: it's expensive, so don't bother
	}
	elsif (defined($self->{Path})) {               ### it's a simple file!
	    $length = (-s $self->{Path})   if (-e $self->{Path});
	}
    }
    $self->attr('content-length' => $length);
    return $length;
}

#------------------------------

=item replace TAG,VALUE

Delete all occurences of fields named TAG, and add a new
field with the given VALUE.  TAG is converted to all-lowercase.

B<Beware> the special MIME fields (MIME-version, Content-*):
if you "replace" a MIME field, the replacement text will override 
the I<actual> MIME attributes when it comes time to output that field.
So normally you use attr() to change MIME fields and add()/replace() to 
change I<non-MIME> fields:

    $msg->replace("Subject" => "Hi there!");

Giving VALUE as the I<empty string> will effectively I<prevent> that
field from being output.  This is the correct way to suppress
the special MIME fields:    

    $msg->replace("Content-disposition" => "");

Giving VALUE as I<undefined> will just cause all explicit values
for TAG to be deleted, without having any new values added.

I<Note:> the name of this method  comes from Mail::Header.

=cut

sub replace {
    my ($self, $tag, $value) = @_;
    $self->delete($tag);
    $self->add($tag, $value) if defined($value);
}


#------------------------------

=item scrub

B<This is Alpha code.  If you use it, please let me know how it goes.>
Recursively goes through the "parts" tree of this message and tries 
to find MIME attributes that can be removed. 
With an array argument, removes exactly those attributes; e.g.:

    $msg->scrub(['content-disposition', 'content-length']);

Is the same as recursively doing:

    $msg->replace('Content-disposition' => '');
    $msg->replace('Content-length'      => '');

=cut

sub scrub {
    my ($self, @a) = @_;
    my ($expl) = @a;
    local $QUIET = 1;

    ### Scrub me:
    if (!@a) {         ### guess

	### Scrub length always:
	$self->replace('content-length', '');

	### Scrub disposition if no filename, or if content-type has same info:
	if (!$self->_safe_attr('content-disposition.filename') ||
	    $self->_safe_attr('content-type.name')) {
	    $self->replace('content-disposition', '');
	}

	### Scrub encoding if effectively unencoded:
	if ($self->_safe_attr('content-transfer-encoding') =~
	    /^(7bit|8bit|binary)$/i) {
	    $self->replace('content-transfer-encoding', '');
	}

	### Scrub charset if US-ASCII:
	if ($self->_safe_attr('content-type.charset') =~ /^(us-ascii)/i) {
	    $self->attr('content-type.charset' => undef);
	}

	### TBD: this is not really right for message/digest:
	if ((keys %{$self->{Attrs}{'content-type'}} == 1) and
	    ($self->_safe_attr('content-type') eq 'text/plain')) {
	    $self->replace('content-type', '');
	}
    }
    elsif ($expl and (ref($expl) eq 'ARRAY')) {
	foreach (@{$expl}) { $self->replace($_, ''); }
    }

    ### Scrub my kids:
    foreach (@{$self->{Parts}}) { $_->scrub(@a); }
}

=back

=cut


#==============================
#==============================

=head2 Setting/getting message data

=over 4

=cut

#------------------------------

=item binmode [OVERRIDE]

With no argument, returns whether or not it thinks that the data 
(as given by the "Path" argument of C<build()>) should be read using 
binmode() (for example, when C<read_now()> is invoked).

The default behavior is that any content type other than 
C<text/*> or C<message/*> is binmode'd; this should in general work fine.

With a defined argument, this method sets an explicit "override"
value.  An undefined argument unsets the override.
The new current value is returned.

=cut

sub binmode {
    my $self = shift;
    $self->{Binmode} = shift if (@_);       ### argument? set override
    return (defined($self->{Binmode}) 
	    ? $self->{Binmode}
	    : ($self->attr("content-type") !~ m{^(text|message)/}i));
}

#------------------------------

=item data [DATA]

Get/set the literal DATA of the message.  The DATA may be
either a scalar, or a reference to an array of scalars (which
will simply be joined).    

I<Warning:> setting the data causes the "content-length" attribute
to be recomputed (possibly to nothing).

=cut

sub data {
    my $self = shift;
    if (@_) {
	$self->{Data} = ((ref($_[0]) eq 'ARRAY') ? join('', @{$_[0]}) : $_[0]);
	$self->get_length;
    }
    $self->{Data};
}


#------------------------------

=item path [PATH]

Get/set the PATH to the message data.

I<Warning:> setting the path recomputes any existing "content-length" field,
and re-sets the "filename" (to the last element of the path if it
looks like a simple path, and to nothing if not).

=cut

sub path {
    my $self = shift;
    if (@_) {

	### Set the path, and invalidate the content length:
	$self->{Path} = shift;

	### Re-set filename, extracting it from path if possible:
	my $filename;
	if ($self->{Path} and ($self->{Path} !~ /\|$/)) {  ### non-shell path:
	    ($filename = $self->{Path}) =~ s/^<//;    
	    ($filename) = ($filename =~ m{([^\/]+)\Z});
	}
	$self->filename($filename);

	### Reset the length:
	$self->get_length;
    }
    $self->{Path};
}

#------------------------------

=item fh [FILEHANDLE]

Get/set the FILEHANDLE which contains the message data.

Takes a filehandle as an input and stores it in the object.
This routine is similar to path(); one important difference is that 
no attempt is made to set the content length.  

=cut

sub fh {
    my $self = shift;
    $self->{FH} = shift if @_;
    $self->{FH};
}

#------------------------------

=item resetfh [FILEHANDLE]

Set the current position of the filehandle back to the beginning. 
Only applies if you used "FH" in build() or attach() for this message.

Returns false if unable to reset the filehandle (since not all filehandles
are seekable).

=cut

#----
# Miko's note: With the Data and Path, the same data could theoretically 
# be reused.  However, file handles need to be reset to be reused, 
# so I added this routine.
#
# Eryq reply: beware... not all filehandles are seekable (think about STDIN)!

sub resetfh {
    my $self = shift;
    seek($self->{FH},0,0);
}

#------------------------------

=item read_now 

Forces data from the path/filehandle (as specified by C<build()>)
to be read into core immediately, just as though you had given it
literally with the C<Data> keyword.  

Note that the in-core data will always be used if available.

Be aware that everything is slurped into a giant scalar: you may not want 
to use this if sending tar files!  The benefit of I<not> reading in the data 
is that very large files can be handled by this module if left on disk
until the message is output via C<print()> or C<print_body()>.

=cut

sub read_now {
    my $self = shift;
    local $/ = undef;
    
    if    ($self->{FH}) {       ### data from a filehandle:
	my $chunk;
	my @chunks;
	CORE::binmode($self->{FH}) if $self->binmode;
	while (read($self->{FH}, $chunk, 1024)) { 
	    push @chunks, $chunk; 
	}
	$self->{Data} = join '', @chunks;
    }
    elsif ($self->{Path}) {     ### data from a path:
	open SLURP, $self->{Path} or croak "open $self->{Path}: $!\n";
	CORE::binmode(SLURP) if $self->binmode;
	$self->{Data} = <SLURP>;        ### sssssssssssssslurp...
	close SLURP;                    ### ...aaaaaaaaahhh!
    }
}

#------------------------------

=item sign PARAMHASH

Sign the message.  This forces the message to be read into core,
after which the signature is appended to it.

=over 4

=item Data

As in C<build()>: the literal signature data.
Can be either a scalar or a ref to an array of scalars.

=item Path

As in C<build()>: the path to the file.

=back

If no arguments are given, the default is:

    Path => "$ENV{HOME}/.signature"

The content-length is recomputed.

=cut

sub sign {
    my $self = shift;
    my %params = @_;

    ### Default:
    @_ or $params{Path} = "$ENV{HOME}/.signature";

    ### Force message in-core:
    defined($self->{Data}) or $self->read_now;

    ### Load signature:
    my $sig;
    if (!defined($sig = $params{Data})) {      ### not given explicitly:
	local $/ = undef;
	open SIG, $params{Path} or croak "open sig $params{Path}: $!\n";
	$sig = <SIG>;                  ### sssssssssssssslurp...
	close SIG;                     ### ...aaaaaaaaahhh!
    }    
    $sig = join('',@$sig) if (ref($sig) and (ref($sig) eq 'ARRAY'));

    ### Append, following Internet conventions:
    $self->{Data} .= "\n-- \n$sig";

    ### Re-compute length:
    $self->get_length;
    1;
}

#------------------------------
#
# =item suggest_encoding CONTENTTYPE
#
# I<Class/instance method.>
# Based on the CONTENTTYPE, return a good suggested encoding.
# C<text> and C<message> types have their bodies scanned line-by-line
# for 8-bit characters and long lines; lack of either means that the
# message is 7bit-ok.  Other types are chosen independent of their body:
#
#    Major type:       7bit ok?    Suggested encoding:
#    ------------------------------------------------------------
#    text              yes         7bit
#                      no          quoted-printable    
#                      unknown     binary
#
#    message           yes         7bit
#                      no          binary    
#                      unknown     binary
#
#    multipart         n/a         binary (in case some parts are not ok)
#
#    (other)           n/a         base64
#
#=cut

sub suggest_encoding {
    my ($self, $ctype) = @_;

    my ($type) = split '/', lc($ctype);
    if (($type eq 'text') || ($type eq 'message')) {    ### scan message body
	return 'binary';
    }
    else {
	return ($type eq 'multipart') ? 'binary' : 'base64';
    }
}

#------------------------------

=item verify_data

I<Instance method.>
Verify that all "paths" to attached data exist, recursively.  
It might be a good idea for you to do this before a print(), to
prevent accidental partial output if a file might be missing.
Raises exception if any path is not readable.

=cut

sub verify_data {
    my $self = shift;

    ### Verify self:
    my $path = $self->{Path};
    if ($path and ($path !~ /\|$/)) {  ### non-shell path:
	$path =~ s/^<//;    
	(-r $path) or die "$path: not readable\n";
    }

    ### Verify parts:
    foreach my $part (@{$self->{Parts}}) { $part->verify_data }
    1;
}

=back

=cut


#==============================
#==============================

=head2 Output

=over 4

=cut

#------------------------------

=item print [OUTHANDLE]

I<Instance method.> 
Print the message to the given output handle, or to the currently-selected
filehandle if none was given.

All OUTHANDLE has to be is a filehandle (possibly a glob ref), or 
any object that responds to a print() message.

=cut

sub print {
    my ($self, $out) = @_;

    ### Coerce into a printable output handle:
    $out = wrap MIME::Lite::IO_Handle $out;

    ### Output head, separator, and body:
    $out->print($self->header_as_string, "\n");
    $self->print_body($out);
}

#------------------------------
#
# print_for_smtp
#
# Instance method, private.
# Print, but filter out the topmost "Bcc" field.
# This is because qmail apparently doesn't do this for us!
#
sub print_for_smtp {
    my ($self, $out) = @_;

    ### Coerce into a printable output handle:
    $out = wrap MIME::Lite::IO_Handle $out;
    
    ### Create a safe head:
    my @fields = grep { $_->[0] ne 'bcc' } @{$self->fields};
    my $header = $self->fields_as_string(\@fields);

    ### Output head, separator, and body:
    $out->print($header, "\n");
    $self->print_body($out);
}

#------------------------------

=item print_body [OUTHANDLE]

I<Instance method.> 
Print the body of a message to the given output handle, or to 
the currently-selected filehandle if none was given.  

All OUTHANDLE has to be is a filehandle (possibly a glob ref), or 
any object that responds to a print() message.

B<Fatal exception> raised if unable to open any of the input files,
or if a part contains no data, or if an unsupported encoding is 
encountered.

=cut

sub print_body {
    my ($self, $out) = @_;

    ### Coerce into a printable output handle:
    $out = wrap MIME::Lite::IO_Handle $out;

    ### Output either the body or the parts.
    ###   Notice that we key off of the content-type!  We expect fewer 
    ###   accidents that way, since the syntax will always match the MIME type.
    my $type = $self->attr('content-type');
    if ($type =~ m{^multipart/}i) {	
	my $boundary = $self->attr('content-type.boundary');

	### Preamble:
	$out->print("This is a multi-part message in MIME format.\n");
	
	### Parts:
	my $part;
	foreach $part (@{$self->{Parts}}) {
	    $out->print("\n--$boundary\n");
	    $part->print($out);
	}

	### Epilogue:
	$out->print("\n--$boundary--\n\n");
    }
    elsif ($type =~ m{^message/}) {
	my @parts = @{$self->{Parts}};

	### It's a toss-up; try both data and parts:
	if    (@parts == 0) { $self->print_simple_body($out) }
	elsif (@parts == 1) { $parts[0]->print($out) }
	else                { croak "can't handle message with >1 part\n"; }
    }
    else {                    
	$self->print_simple_body($out); 
    }
    1;
}

#------------------------------
#
# print_simple_body [OUTHANDLE]
#
# I<Instance method, private.>
# Print the body of a simple singlepart message to the given 
# output handle, or to the currently-selected filehandle if none 
# was given.  
#
# Note that if you want to print "the portion after
# the header", you don't want this method: you want 
# L<print_body()|/print_body>.
#
# All OUTHANDLE has to be is a filehandle (possibly a glob ref), or 
# any object that responds to a print() message.
#
# B<Fatal exception> raised if unable to open any of the input files,
# or if a part contains no data, or if an unsupported encoding is 
# encountered.
#
sub print_simple_body {
    my ($self, $out) = @_;

    ### Coerce into a printable output handle:
    $out = wrap MIME::Lite::IO_Handle $out;

    ### Get content-transfer-encoding:
    my $encoding = uc($self->attr('content-transfer-encoding'));

    ### Notice that we don't just attempt to slurp the data in from a file:
    ### by processing files piecemeal, we still enable ourselves to prepare
    ### very large MIME messages...

    ### Is the data in-core?  If so, blit it out...
    if (defined($self->{Data})) {
      DATA: 
	{ $_ = $encoding;

	  /^BINARY$/ and do {
	      $out->print($self->{Data}); 
	      last DATA;
	  };
	  /^8BIT$/ and do {
	      $out->print(encode_8bit($self->{Data})); 
	      last DATA;
	  };
	  /^7BIT$/ and do {
	      $out->print(encode_7bit($self->{Data})); 
	      last DATA;
	  };
	  /^QUOTED-PRINTABLE$/ and do {
	      while ($self->{Data}=~ m{^(.*[\r\n]*)}mg) {
		  $out->print(encode_qp($1)); ### have to do it line by line...
	      }
	      last DATA;	 
	  };
	  /^BASE64/ and do {
	      $out->print(encode_base64($self->{Data})); 
	      last DATA;
	  };
	  croak "unsupported encoding: `$_'\n";
        }
    }

    ### Else, is the data in a file?  If so, output piecemeal...
    ###    Miko's note: this routine pretty much works the same with a path 
    ###    or a filehandle. the only difference in behaviour is that it does 
    ###    not attempt to open anything if it already has a filehandle
    elsif (defined($self->{Path}) || defined($self->{FH})) {
	no strict 'refs';          ### in case FH is not an object
	my $DATA;

	### Open file if necessary:
	if (defined($self->{Path})) {
	    $DATA = new FileHandle || croak "can't get new filehandle\n";
	    $DATA->open("$self->{Path}") or croak "open $self->{Path}: $!\n";
	}
	else {
	    $DATA=$self->{FH};
	}
	CORE::binmode($DATA) if $self->binmode;
		
	### Encode piece by piece:
      PATH: 
	{   $_ = $encoding;
	    
	    /^BINARY$/ and do {
		$out->print($_)                while read($DATA, $_, 2048); 
		last PATH;
	    };      
	    /^8BIT$/ and do {
		$out->print(encode_8bit($_))   while (<$DATA>); 
		last PATH;
	    };
	    /^7BIT$/ and do {
		$out->print(encode_7bit($_))   while (<$DATA>); 
		last PATH;
	    };
	    /^QUOTED-PRINTABLE$/ and do {
		$out->print(encode_qp($_))     while (<$DATA>); 
		last PATH;
	    };
	    /^BASE64$/ and do {
		$out->print(encode_base64($_)) while (read($DATA, $_, 45));
		last PATH;
	    };
	    croak "unsupported encoding: `$_'\n";
	}
	
	### Close file:
	close $DATA if defined($self->{Path});
    }
    
    else {
	croak "no data in this part\n";
    }
    1;
}

#------------------------------

=item print_header [OUTHANDLE]

I<Instance method.> 
Print the header of the message to the given output handle, 
or to the currently-selected filehandle if none was given.

All OUTHANDLE has to be is a filehandle (possibly a glob ref), or 
any object that responds to a print() message.

=cut

sub print_header {
    my ($self, $out) = @_;

    ### Coerce into a printable output handle:
    $out = wrap MIME::Lite::IO_Handle $out;

    ### Output the header:
    $out->print($self->header_as_string);
    1;
}

#------------------------------

=item as_string

I<Instance method.> 
Return the entire message as a string, with a header and an encoded body.

=cut

sub as_string {
    my $self = shift;
    my @buf;
    my $io = (wrap MIME::Lite::IO_ScalarArray \@buf);
    $self->print($io);
    join '', @buf;
}
*stringify = \&as_string;    ### backwards compatibility

#------------------------------

=item body_as_string

I<Instance method.> 
Return the encoded body as a string.
This is the portion after the header and the blank line.

I<Note:> actually prepares the body by "printing" to a scalar.
Proof that you can hand the C<print*()> methods any blessed object 
that responds to a C<print()> message.

=cut

sub body_as_string {
    my $self = shift;
    my @buf;
    my $io = (wrap MIME::Lite::IO_ScalarArray \@buf);
    $self->print_body($io);
    join '', @buf;
}
*stringify_body = \&body_as_string;    ### backwards compatibility

#------------------------------
#
# fields_as_string FIELDS
#
# PRIVATE!  Return a stringified version of the given header
# fields, where FIELDS is an arrayref like that returned by fields().
#
sub fields_as_string {
    my ($self, $fields) = @_;
    my @lines;
    foreach (@$fields) {
	my ($tag, $value) = @$_; 
	next if ($value eq '');          ### skip empties
	$tag =~ s/\b([a-z])/uc($1)/ge;   ### make pretty
	$tag =~ s/^mime-/MIME-/ig;       ### even prettier
	push @lines, "$tag: $value\n";
    }
    join '', @lines;
}

#------------------------------

=item header_as_string

I<Instance method.> 
Return the header as a string.

=cut

sub header_as_string {
    my $self = shift;
    $self->fields_as_string($self->fields);
}
*stringify_header = \&header_as_string;    ### backwards compatibility

=back

=cut



#==============================
#==============================

=head2 Sending

=over 4

=cut

#------------------------------

=item send

=item send HOW, HOWARGS...

I<Class/instance method.>  
This is the principal method for sending mail, and for configuring
how mail will be sent.

I<As an instance method> (with no arguments), sends the message by whatever 
means has been set up (the default is to use the Unix "sendmail" program).
Returns whatever the mail-handling routine returns: this should be true 
on success, false/exception on error:

    $msg = MIME::Lite->new(From=>...);
    $msg->send || die "you DON'T have mail!";

I<As a class method> (with a HOW argument and optional HOWARGS), sets up 
how the instance method will work for all objects until further notice
It treats HOW as a facility name, with optional HOWARGS handled by
the facility (and returns the previous HOW and HOWARGS as an array).
There are three facilities:

=over 4

=item "sendmail", ARGS...

Send a message by piping it into the "sendmail" command.
Uses the L<send_by_sendmail()|/send_by_sendmail> method, giving it the ARGS.
This usage implements (and deprecates) the C<sendmail()> method.

=item "smtp", [HOSTNAME]

Send a message by SMTP, using optional HOSTNAME as SMTP-sending host.
Uses the L<send_by_smtp()|/send_by_smtp> method.

=item "sub", \&SUBREF, ARGS...

Sends a message MSG by invoking the subroutine SUBREF of your choosing,
with MSG as the first argument, and ARGS following.

=back

I<For example:> let's say you're on an OS which lacks the usual Unix
"sendmail" facility, but you've installed something a lot like it, and
you need to configure your Perl script to use this "sendmail.exe" program.
Do this following in your script's setup:

    MIME::Lite->send('sendmail', "d:\\programs\\sendmail.exe");

Then, whenever you need to send a message $msg, just say:

    $msg->send;

That's it.  Now, if you ever move your script to a Unix box, all you
need to do is change that line in the setup and you're done.
All of your $msg-E<gt>send invocations will work as expected.

=cut

sub send {
    my $self = shift;

    if (ref($self)) {              ### instance method:
	my $method = "send_by_$Sender";
	my @args   = @{$SenderArgs{$Sender} || []};
	$self->verify_data if $AUTO_VERIFY;       ### prevents missing parts!
	return $self->$method(@args);
    }
    else {                         ### class method:
	my @old = ($Sender, @{$SenderArgs{$Sender}});
	$Sender = shift;
	$SenderArgs{$Sender} = [@_];    ### remaining args
	return @old;
    }
}

#------------------------------

=item send_by_sendmail SENDMAILCMD

=item send_by_sendmail PARAM=>VALUE, ...

I<Instance method.>
Send message via an external "sendmail" program
(this will probably only work out-of-the-box on Unix systems).

Returns true on success, false or exception on error.

You can specify the program and all its arguments by giving a single
string, SENDMAILCMD.  Nothing fancy is done; the message is simply
piped in.

However, if your needs are a little more advanced, you can specify 
zero or more of the following PARAM/VALUE pairs; a Unix-style, 
taint-safe "sendmail" command will be constructed for you:

=over 4

=item Sendmail

Full path to the program to use.  
Default is "/usr/lib/sendmail".

=item BaseArgs

Ref to the basic array of arguments we start with.  
Default is C<["-t", "-oi", "-oem"]>.

=item SetSender

Unless this is I<explicitly> given as false, we attempt to automatically
set the C<-f> argument to the first address that can be extracted from 
the "From:" field of the message (if there is one). 

I<What is the -f, and why do we use it?>
Suppose we did I<not> use C<-f>, and you gave an explicit "From:" 
field in your message: in this case, the sendmail "envelope" would 
indicate the I<real> user your process was running under, as a way 
of preventing mail forgery.  Using the C<-f> switch causes the sender 
to be set in the envelope as well.

I<So when would I NOT want to use it?>
If sendmail doesn't regard you as a "trusted" user, it will permit
the C<-f> but also add an "X-Authentication-Warning" header to the message
to indicate a forged envelope.  To avoid this, you can either 
(1) have SetSender be false, or 
(2) make yourself a trusted user by adding a C<T> configuration 
    command to your I<sendmail.cf> file 
    (e.g.: C<Teryq> if the script is running as user "eryq").

=item FromSender

If defined, this is identical to setting SetSender to true, 
except that instead of looking at the "From:" field we use 
the address given by this option.
Thus:

    FromSender => 'me@myhost.com'

=back

=cut

sub send_by_sendmail {
    my $self = shift;

    if (@_ == 1) {                    ### Use the given command...
	my $sendmailcmd = shift @_;

	### Do it:
	open SENDMAIL, "|$sendmailcmd" or croak "open |$sendmailcmd: $!\n";
	$self->print(\*SENDMAIL);
	close SENDMAIL;
	return (($? >> 8) ? undef : 1);
    }
    else {                            ### Build the command...
	my %p = @_;
	$p{Sendmail} ||= "/usr/lib/sendmail";   
	
	### Start with the command and basic args:
	my @cmd = ($p{Sendmail}, @{$p{BaseArgs} || ['-t', '-oi', '-oem']});

	### See if we are forcibly setting the sender:
	$p{SetSender} = 1 if defined($p{FromSender});

	### Add the -f argument, unless we're explicitly told NOT to:
	unless (exists($p{SetSender}) and !$p{SetSender}) {
	    my $from = $p{FromSender} || ($self->get('From'))[0];
	    if ($from) {
		my ($from_addr) = extract_addrs($from);
		push @cmd, "-f$from_addr"       if $from_addr;
	    }
	}

	### Open the command in a taint-safe fashion:
	my $pid = open SENDMAIL, "|-"; 
	defined($pid) or die "open of pipe failed: $!\n";
	if (!$pid) {    ### child
	    exec(@cmd) or die "can't exec $p{Sendmail}: $!\n";
	    ### NOTREACHED
	}
	else {          ### parent
	    $self->print(\*SENDMAIL);
	    close SENDMAIL || die "error closing $p{Sendmail}: $! (exit $?)\n";
	    return 1;
	}
    }
}

#------------------------------

=item send_by_smtp ARGS...

I<Instance method.>
Send message via SMTP, using Net::SMTP.  
The optional ARGS are sent into Net::SMTP::new(): usually, these are

    MAILHOST, OPTION=>VALUE, ...

Note that the list of recipients is taken from the 
"To", "Cc" and "Bcc" fields.

Returns true on success, false or exception on error.

=cut

### Provided by Andrew McRae. Version 0.2  anm  09Sep97
### Copyright 1997 Optimation New Zealand Ltd.
### May be modified/redistributed under the same terms as Perl.
#
sub send_by_smtp {
    my ($self, @args) = @_;

    ### We need the "From:" and "To:" headers to pass to the SMTP mailer:
    my $hdr  = $self->fields();   
    my $from = $self->get('From');
    my $to   = $self->get('To');

    ### Sanity check:
    defined($to) or croak "send_by_smtp: missing 'To:' address\n";
 	       
    ### Get the destinations as a simple array of addresses:
    my @to_all = extract_addrs($to);
    if ($AUTO_CC) {
	foreach my $field (qw(Cc Bcc)) {
	    my $value = $self->get($field);
	    push @to_all, extract_addrs($value) if defined($value);
	}
    }

    ### Create SMTP client:
    require Net::SMTP;
    my $smtp = MIME::Lite::SMTP->new(@args)
        or croak "Failed to connect to mail server: $!\n";
    $smtp->mail($from)
        or croak "SMTP MAIL command failed: $!\n";
    $smtp->to(@to_all)
        or croak "SMTP RCPT command failed: $!\n";
    $smtp->data()
        or croak "SMTP DATA command failed: $!\n";

    ### MIME::Lite can print() to anything with a print() method:
    $self->print_for_smtp($smtp);
    $smtp->dataend();
    $smtp->quit;
    1;
}

#------------------------------
#
# send_by_sub [\&SUBREF, [ARGS...]]
#
# I<Instance method, private.>
# Send the message via an anonymous subroutine.
#
sub send_by_sub {
    my ($self, $subref, @args) = @_;
    &$subref($self, @args);
}

#------------------------------

=item sendmail COMMAND...

I<Class method, DEPRECATED.>  
Declare the sender to be "sendmail", and set up the "sendmail" command.
I<You should use send() instead.>

=cut

sub sendmail {
    my $self = shift;
    $self->send('sendmail', join(' ', @_));
}

=back

=cut



#==============================
#==============================

=head2 Miscellaneous

=over 4

=cut

#------------------------------

=item quiet ONOFF

I<Class method.>  
Suppress/unsuppress all warnings coming from this module.

    MIME::Lite->quiet(1);       ### I know what I'm doing

I recommend that you include that comment as well.  And while
you type it, say it out loud: if it doesn't feel right, then maybe
you should reconsider the whole line.  C<;-)>

=cut

sub quiet {
    my $class = shift;
    $QUIET = shift if @_;
    $QUIET;
}

=back

=cut



#============================================================

package MIME::Lite::SMTP;

#============================================================
# This class just adds a print() method to Net::SMTP.
# Notice that we don't use/require it until it's needed!

use strict;
use vars qw( @ISA );
@ISA = qw(Net::SMTP);

sub print { shift->datasend(@_) }



#============================================================

package MIME::Lite::IO_Handle;

#============================================================

### Wrap a non-object filehandle inside a blessed, printable interface:
### Does nothing if the given $fh is already a blessed object.
sub wrap {
    my ($class, $fh) = @_;
    no strict 'refs';

    ### Get default, if necessary:
    $fh or $fh = select;        ### no filehandle means selected one
    ref($fh) or $fh = \*$fh;    ### scalar becomes a globref
    
    ### Stop right away if already a printable object:
    return $fh if (ref($fh) and (ref($fh) ne 'GLOB'));

    ### Get and return a printable interface:
    bless \$fh, $class;         ### wrap it in a printable interface
}

### Print:
sub print {
    my $self = shift;
    print {$$self} @_;
}


#============================================================

package MIME::Lite::IO_Scalar;

#============================================================

### Wrap a scalar inside a blessed, printable interface:
sub wrap {
    my ($class, $scalarref) = @_;
    defined($scalarref) or $scalarref = \"";
    bless $scalarref, $class;
}

### Print:
sub print {
    my $self = shift;
    $$self .= join('', @_);
    1;
}


#============================================================

package MIME::Lite::IO_ScalarArray;

#============================================================

### Wrap an array inside a blessed, printable interface:
sub wrap {
    my ($class, $arrayref) = @_;
    defined($arrayref) or $arrayref = [];
    bless $arrayref, $class;
}

### Print:
sub print {
    my $self = shift;
    push @$self, @_;
    1;
}

1;
__END__


#============================================================

=head1 NOTES


=head2 Benign limitations

This is "lite", after all...

=over 4

=item *

There's no parsing.  Get MIME-tools if you need to parse MIME messages.

=item *

MIME::Lite messages are currently I<not> interchangeable with 
either Mail::Internet or MIME::Entity objects.  This is a completely 
separate module.

=item *

A content-length field is only inserted if the encoding is binary,
the message is a singlepart, and all the document data is available
at C<build()> time by virtue of residing in a simple path, or in-core.
Since content-length is not a standard MIME field anyway (that's right, kids:
it's not in the MIME RFCs, it's an HTTP thing), this seems pretty fair.

=item *

MIME::Lite alone cannot help you lose weight.  You must supplement
your use of MIME::Lite with a healthy diet and exercise. 

=back


=head2 Cheap and easy mailing

I thought putting in a default "sendmail" invocation wasn't too bad an 
idea, since a lot of Perlers are on UNIX systems. 
The out-of-the-box configuration is:

     MIME::Lite->send('sendmail', "/usr/lib/sendmail -t -oi -oem");

By the way, these arguments to sendmail are:

     -t      Scan message for To:, Cc:, Bcc:, etc.
              
     -oi     Do NOT treat a single "." on a line as a message terminator.
             As in, "-oi vey, it truncated my message... why?!"
                
     -oem    On error, mail back the message (I assume to the
             appropriate address, given in the header).
             When mail returns, circle is complete.  Jai Guru Deva -oem.

Note that these are the same arguments you get if you configure to use
the smarter, taint-safe mailing:

     MIME::Lite->send('sendmail');

If you get "X-Authentication-Warning" headers from this, you can forgo
diddling with the envelope by instead specifying:

     MIME::Lite->send('sendmail', SetSender=>0);

And, if you're not on a Unix system, or if you'd just rather send mail
some other way, there's always:

     MIME::Lite->send('smtp', "smtp.myisp.net");

Or you can set up your own subroutine to call.
In any case, check out the L<send()|/send> method. 



=head1 WARNINGS

=head2 Good-vs-bad email addresses with send_by_smtp()

If using L<send_by_smtp()|/send_by_smtp>, be aware that you are
forcing MIME::Lite to extract email addresses out of a possible list
provided in the C<To:>, C<Cc:>, and C<Bcc:> fields.  This is tricky
stuff, and as such only the following sorts of addresses will work
reliably:

    username
    full.name@some.host.com
    "Name, Full" <full.name@some.host.com>

This last form is discouraged because SMTP must be able to get
at the I<name> or I<name@domain> portion.

B<Disclaimer:>
MIME::Lite was never intended to be a Mail User Agent, so please
don't expect a full implementation of RFC-822.  Restrict yourself to
the common forms of Internet addresses described herein, and you should
be fine.  If this is not feasible, then consider using MIME::Lite
to I<prepare> your message only, and using Net::SMTP explicitly to 
I<send> your message.


=head2 Formatting of headers delayed until print()

This class treats a MIME header in the most abstract sense,
as being a collection of high-level attributes.  The actual
RFC-822-style header fields are not constructed until it's time
to actually print the darn thing.


=head2 Encoding of data delayed until print()

When you specify message bodies 
(in L<build()|/build> or L<attach()|/attach>) -- 
whether by B<FH>, B<Data>, or B<Path> -- be warned that we don't 
attempt to open files, read filehandles, or encode the data until 
L<print()|/print> is invoked.  

In the past, this created some confusion for users of sendmail
who gave the wrong path to an attachment body, since enough of 
the print() would succeed to get the initial part of the message out.  
Nowadays, $AUTO_VERIFY is used to spot-check the Paths given before
the mail facility is employed.  A whisker slower, but tons safer.

Note that if you give a message body via FH, and try to print() 
a message twice, the second print() will not do the right thing 
unless you  explicitly rewind the filehandle.

You can get past these difficulties by using the B<ReadNow> option,
provided that you have enough memory to handle your messages.


=head2 MIME attributes are separate from header fields!

B<Important:> the MIME attributes are stored and manipulated separately 
from the message header fields; when it comes time to print the 
header out, I<any explicitly-given header fields override the ones that
would be created from the MIME attributes.>  That means that this:

    ### DANGER ### DANGER ### DANGER ### DANGER ### DANGER ###
    $msg->add("Content-type", "text/html; charset=US-ASCII");

will set the exact C<"Content-type"> field in the header I write, 
I<regardless of what the actual MIME attributes are.>

I<This feature is for experienced users only,> as an escape hatch in case
the code that normally formats MIME header fields isn't doing what 
you need.  And, like any escape hatch, it's got an alarm on it:
MIME::Lite will warn you if you attempt to C<set()> or C<replace()>
any MIME header field.  Use C<attr()> instead.


=head2 Beware of lines consisting of a single dot

Julian Haight noted that MIME::Lite allows you to compose messages
with lines in the body consisting of a single ".".  
This is true: it should be completely harmless so long as "sendmail" 
is used with the -oi option (see L<"Cheap and easy mailing">).

However, I don't know if using Net::SMTP to transfer such a message
is equally safe.  Feedback is welcomed.

My perspective: I don't want to magically diddle with a user's 
message unless absolutely positively necessary.  
Some users may want to send files with "." alone on a line;
my well-meaning tinkering could seriously harm them.



=head1 A MIME PRIMER

=head2 Content types

The "Type" parameter of C<build()> is a I<content type>. 
This is the actual type of data you are sending.  
Generally this is a string of the form C<"majortype/minortype">.

Here are the major MIME types.
A more-comprehensive listing may be found in RFC-2046.

=over 4

=item application

Data which does not fit in any of the other categories, particularly 
data to be processed by some type of application program. 
C<application/octet-stream>, C<application/gzip>, C<application/postscript>...

=item audio

Audio data.
C<audio/basic>...

=item image

Graphics data.
C<image/gif>, C<image/jpeg>...

=item message

A message, usually another mail or MIME message.
C<message/rfc822>...

=item multipart

A message containing other messages.
C<multipart/mixed>, C<multipart/alternative>...

=item text

Textual data, meant for humans to read.
C<text/plain>, C<text/html>...

=item video

Video or video+audio data.
C<video/mpeg>...

=back


=head2 Content transfer encodings

The "Encoding" parameter of C<build()>.
This is how the message body is packaged up for safe transit.

Here are the 5 major MIME encodings.
A more-comprehensive listing may be found in RFC-2045.

=over 4

=item 7bit

Basically, no I<real> encoding is done.  However, this label guarantees that no
8-bit characters are present, and that lines do not exceed 1000 characters 
in length.

=item 8bit

Basically, no I<real> encoding is done.  The message might contain 8-bit 
characters, but this encoding guarantees that lines do not exceed 1000 
characters in length.

=item binary

No encoding is done at all.  Message might contain 8-bit characters,
and lines might be longer than 1000 characters long.

The most liberal, and the least likely to get through mail gateways.  
Use sparingly, or (better yet) not at all.

=item base64

Like "uuencode", but very well-defined.  This is how you should send
essentially binary information (tar files, GIFs, JPEGs, etc.). 

=item quoted-printable

Useful for encoding messages which are textual in nature, yet which contain 
non-ASCII characters (e.g., Latin-1, Latin-2, or any other 8-bit alphabet).

=back



=head1 VERSION

$Id: Lite.pm,v 2.106 2000/11/21 08:16:32 eryq Exp $


=head1 CHANGE LOG

=over 4

=item Version 2.106   (2000/11/21)

Added Alpha version of scrub() to make it easy for people to suppress
the printing of unwanted MIME attributes (like Content-length).
I<Thanks to the many people who asked for this.>

Headers with empty-strings for their values are no longer
printed.  This seems sensible, and helps us implement scrub().


=item Version 2.105   (2000/10/14)

The regression-test failure was identified, and it was my fault.
Apparently some of the \-quoting in my "autoloaded" code was
making Perl 5.6 unhappy.  For this nesting-related idiocy, 
a nesting kaiku.
I<Thanks to Scott Schwartz for identifying the problem.>

    In a pattern, my
       backslash-s dwells peacefully,
    unambiguous --
     
       but I embed it
          in a double-quoted string    
       doubling the backslash --
     
          interpolating
             that same double-quoted string 
          in other patterns --
           
             and, worlds within worlds,
                I single-quote the function
             to autoload it -- 
    
          changing the meaning
       of the backslash and the 's';
    and Five-Point-Six growls.


=item Version 2.104   (2000/09/28)

Now attempts to load and use Mail::Address for parsing email 
addresses I<before> falling back to our own method.
I<Thanks to numerous people for suggesting this.>

    Parsing addresses
       is too damn hard. One last hope:
    Let Graham Barr do it!

For the curious, the version of Mail::Address appears 
as the "A" number in the X-Mailer:

    X-Mailer: MIME::Lite 2.104  (A1.15; B2.09; Q2.03)

Added B<FromSender> option to send_by_sendmail().
I<Thanks to Bill Moseley for suggesting this feature.>


=item Version 2.101   (2000/06/06)

Major revision to print_body() and body_as_string() so that
"body" really means "the part after the header", which is what most
people would want in this context.  This is B<not> how it was used
1.x, where "body" only meant "the body of a simple singlepart".
Hopefully, this change will solve many problems and create very few ones.  

Added support for attaching a part to a "message/rfc822", treating
the "message" type as a multipart-like container.

Now takes care not to include "Bcc:" in header when using send_by_smtp,
as a safety precaution against qmail's behavior.
I<Thanks to Tatsuhiko Miyagawa for identifying this problem.>

Improved efficiency of many stringifying operations by using 
string-arrays which are joined, instead of doing multiple appends 
to a scalar.

Cleaned up the "examples" directory.


=item Version 1.147   (2000/06/02)

Fixed buglet where lack of Cc:/Bcc: was causing extract_addrs
to emit "undefined variable" warnings.  Also, lack of a "To:" field
now causes a croak.
I<Thanks to David Mitchell for the bug report and suggested patch.>


=item Version 1.146   (2000/05/18)

Fixed bug in parsing of addresses; please read the WARNINGS section
which describes recommended address formats for "To:", "Cc:", etc.
Also added automatic inclusion of a UT "Date:" at top level unless 
explicitly told not to.
I<Thanks to Andy Jacobs for the bug report and the suggestion.>

=item Version 1.145   (2000/05/06)

Fixed bug in encode_7bit(): a lingering C</e> modifier was removed.
I<Thanks to Michael A. Chase for the patch.>


=item Version 1.142   (2000/05/02)

Added new, taint-safe invocation of "sendmail", one which also
sets up the C<-f> option.  Unfortunately, I couldn't make this automatic:
the change could have broken a lot of code out there which used 
send_by_sendmail() with unusual "sendmail" variants.  
So you'll have to configure "send" to use the new mechanism:

    MIME::Lite->send('sendmail');       ### no args!

I<Thanks to Jeremy Howard for suggesting these features.>


=item Version 1.140   (2000/04/27)

Fixed bug in support for "To", "Cc", and "Bcc" in send_by_smtp():
multiple (comma-separated) addresses should now work fine.
We try real hard to extract addresses from the flat text strings.
I<Thanks to John Mason for motivating this change.>

Added automatic verification that attached data files exist,
done immediately before the "send" action is invoked.
To turn this off, set $MIME::Lite::AUTO_VERIFY to false.

=item Version 1.137   (2000/03/22)

Added support for "Cc" and "Bcc" in send_by_smtp().
To turn this off, set $MIME::Lite::AUTO_CC to false.
I<Thanks to Lucas Maneos for the patch, and tons of others for 
the suggestion.>

Chooses a better default content-transfer-encoding if the content-type
is "image/*", "audio/*", etc.
To turn this off, set $MIME::Lite::AUTO_ENCODE to false.
I<Thanks to many folks for the suggestion.>

Fixed bug in QP-encoding where a non-local C<$_> was being modified.
I<Thanks to Jochen Stenzel for finding this very obscure bug!>

Removed references to C<$`>, C<$'>, and C<$&> (bad variables
which slow things down).

Added an example of how to send HTML files with enclosed in-line
images, per popular demand.


=item Version 1.133   (1999/04/17)

Fixed bug in "Data" handling: arrayrefs were not being handled
properly.


=item Version 1.130   (1998/12/14)

Added much larger and more-flexible send() facility.
I<Thanks to Andrew McRae (and Optimation New Zealand Ltd) 
for the Net::SMTP interface.  Additional thanks to the many folks
who requested this feature.>

Added get() method for extracting basic attributes.

New... "t" tests!


=item Version 1.124   (1998/11/13)

Folded in filehandle (FH) support in build/attach.
I<Thanks to Miko O'Sullivan for the code.>


=item Version 1.122   (1998/01/19)

MIME::Base64 and MIME::QuotedPrint are used if available.

The 7bit encoding no longer does "escapes"; it merely strips 8-bit characters.


=item Version 1.121   (1997/04/08)

Filename attribute is now no longer ignored by build().
I<Thanks to Ian Smith for finding and patching this bug.>


=item Version 1.120   (1997/03/29)

Efficiency hack to speed up MIME::Lite::IO_Scalar.
I<Thanks to David Aspinwall for the patch.>


=item Version 1.116   (1997/03/19)

Small bug in our private copy of encode_base64() was patched.
I<Thanks to Andreas Koenig for pointing this out.>

New, prettier way of specifying mail message headers in C<build()>.

New quiet method to turn off warnings.

Changed "stringify" methods to more-standard "as_string" methods.


=item Version 1.112   (1997/03/06)

Added C<read_now()>, and C<binmode()> method for our non-Unix-using brethren: 
file data is now read using binmode() if appropriate.
I<Thanks to Xiangzhou Wang for pointing out this bug.>


=item Version 1.110   (1997/03/06)

Fixed bug in opening the data filehandle.


=item Version 1.102   (1997/03/01)

Initial release.


=item Version 1.101   (1997/03/01)

Baseline code.

=back


=head1 TERMS AND CONDITIONS

Copyright (c) 1997 by Eryq.  
Copyright (c) 1998 by ZeeGee Software Inc.
All rights reserved.  This program is free software; you can redistribute 
it and/or modify it under the same terms as Perl itself.  

This software comes with B<NO WARRANTY> of any kind.
See the COPYING file in the distribution for details.


=head1 NUTRITIONAL INFORMATION

For some reason, the US FDA says that this is now required by law
on any products that bear the name "Lite"...

    MIME::Lite                | 
    ------------------------------------------------------------
    Serving size:             | 1 module
    Servings per container:   | 1
    Calories:                 | 0
    Fat:                      | 0g
      Saturated Fat:          | 0g

Warning: for consumption by hardware only!  May produce 
indigestion in humans if taken internally.


=head1 AUTHOR

Eryq (F<eryq@zeegee.com>).
President, ZeeGee Software Inc. (F<http://www.zeegee.com>).

Created: 11 December 1996.  Ho ho ho.

=cut


