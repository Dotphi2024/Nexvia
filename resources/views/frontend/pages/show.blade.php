<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->meta_title ?? ($page->title . ' - NEXVIA') }}</title>
    <meta name="description" content="{{ $page->meta_description ?? $page->excerpt }}">
    @if($page->meta_keywords)
        <meta name="keywords" content="{{ $page->meta_keywords }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            line-height: 1.7;
        }
        .hero-banner {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
            color: #ffffff;
            padding: 60px 0 50px;
        }
        .content-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            padding: 40px;
            margin-top: -30px;
            margin-bottom: 60px;
        }
        .point-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #4f46e5;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .point-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.08);
        }
        .point-number {
            display: inline-block;
            background: #4f46e5;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <!-- Top Header -->
    <header class="hero-banner text-center">
        <div class="container">
            <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 mb-2">NEXVIA Official Policy</span>
            <h1 class="fw-bold display-6 mb-2">{{ $page->title }}</h1>
            @if($page->excerpt)
                <p class="lead text-white-50 mx-auto" style="max-width: 650px; font-size: 15px;">{{ $page->excerpt }}</p>
            @endif
            <div class="text-white-50 small mt-2">
                Last updated: {{ $page->updated_at ? $page->updated_at->format('F d, Y') : date('F d, Y') }}
            </div>
        </div>
    </header>

    <main class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="content-card">
                    <!-- Structured Key Points Section -->
                    @if(is_array($page->points) && count($page->points) > 0)
                        <div class="mb-5">
                            <h4 class="fw-bold text-dark mb-3 pb-2 border-bottom">Key Highlights & Terms</h4>
                            <div class="row g-3">
                                @foreach($page->points as $index => $point)
                                    @php
                                        $pTitle = is_array($point) ? ($point['title'] ?? '') : '';
                                        $pDesc = is_array($point) ? ($point['description'] ?? '') : (is_string($point) ? $point : '');
                                    @endphp
                                    @if(!empty($pTitle) || !empty($pDesc))
                                        <div class="col-md-6">
                                            <div class="point-card h-100">
                                                <span class="point-number">Point {{ $loop->iteration }}</span>
                                                @if(!empty($pTitle))
                                                    <h6 class="fw-bold text-dark mb-1">{{ $pTitle }}</h6>
                                                @endif
                                                <p class="text-secondary small mb-0">{{ $pDesc }}</p>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Detailed HTML Body -->
                    @if(!empty($page->content))
                        <div class="page-body border-top pt-4">
                            {!! $page->content !!}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <footer class="text-center py-4 text-muted small border-top bg-white">
        <div class="container">
            © {{ date('Y') }} NEXVIA Technologies. All rights reserved.
        </div>
    </footer>
</body>
</html>
