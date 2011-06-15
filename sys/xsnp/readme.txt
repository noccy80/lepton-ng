eXtensible Social Networking Protocol (XSNP)
--------------------------------------------

BASE:
	xsnp.client - client implementation class
	xsnp.server - server implementation class

EXTRAS:
	xsnp.utils - Utility classes, used by the base classes
	
HANDLERS:
	xsnp.defaults.controller - Implements the xsnp-controller
	

To fully implement support for xsnp for the user, you should add the following
headers to the relevant views:

TO ALL:
	<link rel="xsnp/discover" type="text/xmp+xsnp" href="/xsnp/discover" />
TO PROFILE PAGES:
	<meta name="xsnp-identity" value="username@yourhostname.com" />
	<link rel="xsnp/profile" type="text/xml+xsnp" href="/xsnp/profile" />
