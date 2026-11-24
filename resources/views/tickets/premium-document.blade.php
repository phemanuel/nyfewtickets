<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<style>
    /* A4 landscape — DomPDF-safe fixed measurements. */
    @page { size: A4 landscape; margin: 0; }
    html, body { margin: 0; padding: 0; background: #080706; }
    body { font-family: DejaVu Sans, sans-serif; color: #f8e9be; }
    .page {
        position: relative;
        width: 297mm;
        height: 210mm;
        overflow: hidden;
        background: #0c0907;
        page-break-after: always;
    }
    .page:last-child { page-break-after: auto; }

    /* The artwork is now the hero: 168 / 297mm (57% of page). */
    .poster {
        position: absolute;
        top: 0;
        left: 0;
        width: 168mm;
        height: 210mm;
        overflow: hidden;
    }
    .poster img {
        display: block;
        width: 168mm;
        height: 210mm;
        border: 0;
    }

    /* Compact information column: remaining 129mm (43%). */
    .right {
        position: absolute;
        top: 0;
        left: 168mm;
        width: 129mm;
        height: 210mm;
        background: #0c0907;
        border-left: 1px solid #80612a;
    }
    .topline { height: 2mm; background: #c39c4c; }
    .content { padding: 10mm 10mm 0 10mm; }
    .eyebrow { font-size: 8.5pt; letter-spacing: 1.2px; color: #dabb76; font-weight: bold; }
    .title {
        margin: 4mm 0 2mm;
        font-size: 20pt;
        line-height: 1.2;
        font-weight: bold;
        color: #fff5dd;
        overflow-wrap: break-word;
    }
    .subtitle { font-size: 10pt; color: #d4b668; margin: 0 0 5mm; }
    .rule { border-top: 1px solid #80612a; margin: 5mm 0; }
    .label { font-size: 8pt; text-transform: uppercase; color: #d4b668; letter-spacing: .8px; }
    .value {
        font-size: 17pt;
        line-height: 1.25;
        font-weight: bold;
        color: #fffaf1;
        margin-top: 2mm;
        overflow-wrap: break-word;
    }
    .category { margin: 4mm 0 5mm; }
    .tag { display: inline-block; border: 1px solid #e0b864; padding: 2mm 4mm; color: #f7da96; font-size: 10pt; font-weight: bold; }
    .details { font-size: 9pt; line-height: 1.65; color: #f0dcae; overflow-wrap: break-word; }
    .details strong { color: #fff5dd; }

    .ticket-meta {
        position: absolute;
        top: 128mm;
        left: 10mm;
        width: 60mm;
    }
    .ticket-number { font-size: 10.5pt; line-height: 1.4; color: white; margin-top: 3mm; overflow-wrap: break-word; }
    .ticket-kind { font-size: 8pt; line-height: 1.5; margin-top: 4mm; color: #d5b875; }
    .qr {
        position: absolute;
        right: 9mm;
        top: 121mm;
        width: 41mm;
        height: 41mm;
        padding: 2mm;
        background: #ffffff;
    }
    .qr img { display: block; width: 41mm; height: 41mm; }
    .qr-label { position: absolute; right: 9mm; top: 169mm; width: 45mm; font-size: 7pt; text-align: center; color: #dfc58e; }
    .foot {
        position: absolute;
        left: 10mm;
        right: 10mm;
        bottom: 8mm;
        padding-top: 3mm;
        border-top: 1px solid #6c5737;
        font-size: 7.2pt;
        line-height: 1.55;
        color: #dac083;
    }
</style>
</head>
<body>
@foreach($pages as $page)
    @php($ticket = $page['ticket'])
    <div class="page">
        <div class="poster">
            @php
                $eventPosterPath = $ticket->event->posterFilePath();
                $eventPosterPath = $eventPosterPath ?: public_path('images/nyfew-event-placeholder.png');
                $eventPosterData = base64_encode(file_get_contents($eventPosterPath));
                $eventPosterMime = mime_content_type($eventPosterPath) ?: 'image/png';
            @endphp
            <img src="data:{{ $eventPosterMime }};base64,{{ $eventPosterData }}" alt="{{ $ticket->event->title }} artwork">
        </div>
        <div class="right">
            <div class="topline"></div>
            <div class="content">
                <div class="eyebrow">NYFEW · OFFICIAL ADMISSION PASS</div>
                <div class="title">{{ $ticket->event->title }}</div>
                <div class="subtitle">OFFICIAL NYFEW EVENT · ADMIT ONE</div>
                <div class="rule"></div>
                <div class="label">Registered guest</div>
                <div class="value">{{ $ticket->guest->full_name }}</div>
                <div class="category"><span class="tag">{{ strtoupper($ticket->category->name) }}</span></div>
                <div class="details">
                    <strong>Date:</strong> {{ $ticket->event->starts_at?->format('d F Y') ?? 'See event information' }}<br>
                    <strong>Start:</strong> {{ $ticket->event->starts_at?->format('g:i A') ?? 'See event information' }}<br>
                    <strong>Location:</strong> {{ $ticket->event->venue ?: 'See event information' }}
                </div>
            </div>
            <div class="ticket-meta">
                <div class="label">Ticket reference</div>
                <div class="ticket-number">{{ $ticket->number }}</div>
                <div class="ticket-kind">{{ $ticket->kind === 'paid' ? 'Paid admission' : 'Complimentary invitation' }}</div>
            </div>
            <div class="qr">
                <img src="data:image/png;base64,{{ $page['qrData'] }}" alt="Unique entry QR code">
            </div>
            <div class="qr-label">SCAN AT ENTRANCE</div>
            <div class="foot">ONE PERSON · ONE ENTRY · Present this unique QR at the entrance. Keep your code private.</div>
        </div>
    </div>
@endforeach
</body>
</html>
