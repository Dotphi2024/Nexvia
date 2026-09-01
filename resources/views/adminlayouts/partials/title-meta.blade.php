<meta charset="utf-8" />
<title>{{ isset($settings->site_title) ? $settings->site_title : 'Nexvia' }} | {{ isset($title) ? $title : '' }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Taplox: An advanced, fully responsive admin dashboard template packed with features to streamline your analytics and management needs." />
<meta name="author" content="StackBros" />
<meta name="keywords" content="Taplox, admin dashboard, responsive template, analytics, modern UI, management tools" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="robots" content="index, follow" />
<meta name="theme-color" content="#ffffff">

<!-- App favicon -->
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset(isset($settings) && $settings->favicon ? 'logo/' . $settings->favicon : '/images/favicon.ico') }}" />