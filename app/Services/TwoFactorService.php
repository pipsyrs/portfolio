<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    private const RECOVERY_CODES = 8;

    public function __construct(private readonly Google2FA $engine) {}

    public function generateSecret(): string
    {
        return $this->engine->generateSecretKey(32);
    }

    /**
     * @return array<int,string>
     */
    public function generateRecoveryCodes(): array
    {
        return collect(range(1, self::RECOVERY_CODES))
            ->map(fn () => strtoupper(bin2hex(random_bytes(4)).'-'.bin2hex(random_bytes(4))))
            ->all();
    }

    public function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\D/', '', $code) ?? '';

        if (strlen($code) !== 6) {
            return false;
        }

        // Toleransi satu jendela (±30 detik) untuk jam perangkat yang meleset.
        return (bool) $this->engine->verifyKey($secret, $code, 1);
    }

    public function otpauthUri(User $user, string $secret): string
    {
        $issuer = config('app.name', 'Portfolio');

        return $this->engine->getQRCodeUrl($issuer, (string) $user->email, $secret);
    }

    /**
     * QR dirender sebagai SVG inline — tidak ada permintaan ke layanan luar,
     * jadi secret tidak pernah meninggalkan server ini.
     */
    public function qrCodeSvg(User $user, string $secret, int $size = 200): string
    {
        $writer = new Writer(new ImageRenderer(
            new RendererStyle($size, 0),
            new SvgImageBackEnd,
        ));

        $svg = $writer->writeString($this->otpauthUri($user, $secret));

        // Buang deklarasi XML supaya bisa disisipkan langsung ke HTML.
        return preg_replace('/^<\?xml.*?\?>\s*/', '', $svg) ?? $svg;
    }

    /**
     * Memakai satu recovery code. Mengembalikan sisa kode bila cocok, atau null.
     *
     * @param  array<int,string>  $codes
     * @return array<int,string>|null
     */
    public function consumeRecoveryCode(array $codes, string $input): ?array
    {
        $input = strtoupper(trim($input));

        foreach ($codes as $index => $code) {
            // hash_equals mencegah pembandingan yang bocor lewat waktu.
            if (hash_equals(strtoupper($code), $input)) {
                unset($codes[$index]);

                return array_values($codes);
            }
        }

        return null;
    }
}
