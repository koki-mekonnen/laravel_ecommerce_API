<?php
namespace App\Repositories;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;


class WebhookRepository
{

    function validateWebhookToken(string $tokenString): bool
    {
       $publicKey = <<<EOD
-----BEGIN PUBLIC KEY-----
MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEBy11auaVru1GUhPavhEub2tfx8P6
EvdVnq+BL/fDEe83IwgOrkvgbTa6BEUQkKoqsn+8bpJ3BIYAI2Iqa+KzZw==
-----END PUBLIC KEY-----
EOD;

$resource   = openssl_pkey_get_public($publicKey);
$keyDetails = openssl_pkey_get_details($resource);

if ($keyDetails && $keyDetails['type'] === OPENSSL_KEYTYPE_EC) {
    echo "✅ The key is a valid ECDSA public key.\n";
    echo "Curve: " . $keyDetails['ec']['curve_name'] . "\n";
} else {
    echo "❌ The key is NOT a valid ECDSA public key.\n";
}




        try {
            // Decode token without verification to check header values
            $tokenParts = explode('.', $tokenString);
            $header     = json_decode(base64_decode($tokenParts[0]), true);

            if (! isset($header['alg']) || $header['alg'] !== 'ES256') {
                Log::error('Invalid token algorithm');
                return false;
            }

            if (! isset($header['typ']) || $header['typ'] !== 'JWT') {
                Log::error('Invalid token type');
                return false;
            }

            // Decode and validate the token
            $decoded = JWT::decode($tokenString, new Key($publicKey, 'ES256'));

            // Check the issuer
            if ($decoded->iss !== 'services.santimpay.com') {
                Log::error('Invalid issuer');
                return false;
            }

            // Check the audience
            if (! isset($decoded->aud) || $decoded->aud !== 'services.santimpay.com') {
                Log::error('Invalid audience');
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Token validation failed: " . $e->getMessage());
            return false;
        }
    }


    public function handlewebhook(array $data)
    {

        try {
            $trasnaction=Transaction::create($data);

            return $trasnaction;

        } catch (\Exception $e) {
            Log::error('Error registering transaction: '. $e->getMessage());
            throw $e;

        }

    }

}
