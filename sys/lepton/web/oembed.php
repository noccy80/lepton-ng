<?php

// using('lepton.web.discovery');

abstract class oEmbed {
	const TYP_PHOTO = 'photo';
	const TYP_VIDEO = 'video';
	const TYP_LINK = 'link';
	const TYP_RICH = 'rich';
}

class oEmbedConsumer {

	function __construct($url) {
		// $items = discovery::find($url);
		// if (arr::has($items,'application/xml+oembed')) {
		//    ...
		// }
	}

}

class oEmbedGenerator {




}
