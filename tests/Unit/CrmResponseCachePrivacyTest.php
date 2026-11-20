<?php

namespace Tests\Unit;

use App\Http\Middleware\PreventCrmResponseCaching;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** No DB, no application bootstrap, no authentication fixtures required. */
final class CrmResponseCachePrivacyTest extends TestCase
{
    private function execute(Response $response, string $url = '/reports'): Response
    {
        return (new PreventCrmResponseCaching())->handle(
            Request::create($url, 'GET'),
            static fn (Request $request): Response => $response
        );
    }

    public function test_html_response_cannot_be_stored(): void
    {
        $response = new Response('<h1>CRM</h1>', 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
        $actual = $this->execute($response);
        self::assertSame($response, $actual);
        self::assertStringContainsString('no-store', (string) $actual->headers->get('Cache-Control'));
        self::assertStringContainsString('private', (string) $actual->headers->get('Cache-Control'));
        self::assertStringNotContainsString('public', (string) $actual->headers->get('Cache-Control'));
        self::assertSame('no-cache', $actual->headers->get('Pragma'));
        self::assertSame('nosniff', $actual->headers->get('X-Content-Type-Options'));
    }

    public function test_streamed_invoice_csv_has_no_store_and_preserves_download_headers(): void
    {
        $stream = new StreamedResponse(static function (): void { echo 'a,b'; });
        $stream->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $stream->headers->set('Content-Disposition', 'attachment; filename="invoices.csv"');
        $actual = $this->execute($stream, '/reports/export/invoices');
        self::assertSame($stream, $actual);
        self::assertStringContainsString('no-store', (string) $actual->headers->get('Cache-Control'));
        self::assertSame('attachment; filename="invoices.csv"', $actual->headers->get('Content-Disposition'));
        self::assertStringContainsString('text/csv', (string) $actual->headers->get('Content-Type'));
    }

    public function test_login_redirect_is_not_cached(): void
    {
        $redirect = new Response('', 302, ['Location' => '/login']);
        $actual = $this->execute($redirect, '/');
        self::assertSame(302, $actual->getStatusCode());
        self::assertSame('/login', $actual->headers->get('Location'));
        self::assertStringContainsString('no-store', (string) $actual->headers->get('Cache-Control'));
    }

    public function test_json_response_cannot_be_stored(): void
    {
        $json = new Response('{"ok":true}', 200, ['Content-Type' => 'application/json']);
        $actual = $this->execute($json, '/workspace/notifications');
        self::assertSame('application/json', $actual->headers->get('Content-Type'));
        self::assertStringContainsString('no-store', (string) $actual->headers->get('Cache-Control'));
    }
}
