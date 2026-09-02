<meta charset="utf-8" />
<title>{{ (isset($settings) && is_object($settings) && isset($settings->site_title)) ? $settings->site_title : 'Nexvia' }} | {{ isset($title) ? $title : '' }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="NEXVIA Smart Products & Booking Management Portal" />
<meta name="author" content="NEXVIA" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="theme-color" content="#ffffff">

<!-- App favicon -->
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset((isset($settings) && is_object($settings) && isset($settings->favicon)) ? 'logo/' . $settings->favicon : '/images/favicon.ico') }}" />