<?php

declare(strict_types=1);

namespace App\Services\Pix;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-17
| Descrição: Serviço para geração de PIX BR Code (EMV QRCPS) estático
*/

use App\Repositories\FinancialPaymentsConfigRepository;

class PixBRCodeService
{
    public function __construct(
        protected FinancialPaymentsConfigRepository $configRepository
    ) {}

    /**
     * Gera o payload PIX BR Code para uma loja específica
     * 
     * @param int $lojaId ID da loja
     * @param float $valor Valor da transação
     * @param string $identificador Identificador da transação (TXID, max 25 chars)
     * @return array ['payload' => string, 'qrcode_base64' => string, 'config' => array]
     */
    public function gerarPayload(int $lojaId, float $valor, string $identificador = ''): array
    {
        $config = $this->configRepository->getPixConfigByLojaWithFallback($lojaId);

        if (empty($config['chave'])) {
            throw new \RuntimeException('Chave PIX não configurada para esta loja.');
        }

        // Gera identificador único se não fornecido
        if (empty($identificador)) {
            $identificador = 'VPT' . strtoupper(substr(md5(uniqid()), 0, 12));
        }

        // Sanitiza identificador (max 25 chars, alfanumérico)
        $identificador = preg_replace('/[^A-Za-z0-9]/', '', $identificador);
        $identificador = substr($identificador, 0, 25);

        $payload = $this->montarPayload(
            chave: $config['chave'],
            beneficiario: $config['beneficiario'] ?: 'VAPT GRAFICA',
            cidade: $config['cidade'] ?: 'SAO PAULO',
            valor: $valor,
            identificador: $identificador
        );

        // Gera QR Code em base64
        $qrcodeBase64 = $this->gerarQRCodeBase64($payload);

        return [
            'payload' => $payload,
            'qrcode_base64' => $qrcodeBase64,
            'identificador' => $identificador,
            'config' => [
                'chave' => $this->mascararChave($config['chave']),
                'tipo' => $config['tipo'],
                'beneficiario' => $config['beneficiario'],
                'cidade' => $config['cidade'],
            ],
        ];
    }

    /**
     * Monta o payload EMV QRCPS conforme especificação do BACEN
     */
    protected function montarPayload(
        string $chave,
        string $beneficiario,
        string $cidade,
        float $valor,
        string $identificador
    ): string {
        // 00: Payload Format Indicator
        $payload = $this->pad('00', '01');

        // 26: Merchant Account Information (PIX)
        $gui = $this->pad('00', 'br.gov.bcb.pix');
        $key = $this->pad('01', $chave);
        $payload .= $this->pad('26', $gui . $key);

        // 52: Merchant Category Code (0000 = não informado)
        $payload .= $this->pad('52', '0000');

        // 53: Transaction Currency (986 = BRL)
        $payload .= $this->pad('53', '986');

        // 54: Transaction Amount (opcional, mas sempre incluímos)
        if ($valor > 0) {
            $payload .= $this->pad('54', number_format($valor, 2, '.', ''));
        }

        // 58: Country Code
        $payload .= $this->pad('58', 'BR');

        // 59: Merchant Name (max 25 chars, sem acentos)
        $beneficiarioLimpo = $this->removerAcentos(mb_substr($beneficiario, 0, 25));
        $payload .= $this->pad('59', $beneficiarioLimpo);

        // 60: Merchant City (max 15 chars, sem acentos)
        $cidadeLimpa = $this->removerAcentos(mb_substr($cidade, 0, 15));
        $payload .= $this->pad('60', $cidadeLimpa);

        // 62: Additional Data Field Template (TXID)
        if (!empty($identificador)) {
            $txid = $this->pad('05', $identificador);
            $payload .= $this->pad('62', $txid);
        }

        // 63: CRC16 (4 caracteres hex)
        $payload .= '6304';
        $payload .= $this->calcularCRC16($payload);

        return $payload;
    }

    /**
     * Formata campo EMV: ID (2 dígitos) + Tamanho (2 dígitos) + Valor
     */
    protected function pad(string $id, string $valor): string
    {
        return $id . str_pad((string) strlen($valor), 2, '0', STR_PAD_LEFT) . $valor;
    }

    /**
     * Calcula CRC16-CCITT (polinômio 0x1021)
     */
    protected function calcularCRC16(string $payload): string
    {
        $resultado = 0xFFFF;
        $bytes = unpack('C*', $payload);

        foreach ($bytes as $byte) {
            $resultado ^= ($byte << 8);
            for ($i = 0; $i < 8; $i++) {
                if (($resultado & 0x8000) !== 0) {
                    $resultado = ($resultado << 1) ^ 0x1021;
                } else {
                    $resultado <<= 1;
                }
            }
        }

        return strtoupper(str_pad(dechex($resultado & 0xFFFF), 4, '0', STR_PAD_LEFT));
    }

    /**
     * Remove acentos de uma string
     */
    protected function removerAcentos(string $string): string
    {
        $acentos = [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
            'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A', 'Ä' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Õ' => 'O', 'Ô' => 'O', 'Ö' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ç' => 'C', 'Ñ' => 'N',
        ];

        return strtr($string, $acentos);
    }

    /**
     * Gera QR Code em base64 usando API do Google Charts
     * (alternativa: usar pacote como simplesoftwareio/simple-qrcode)
     */
    protected function gerarQRCodeBase64(string $payload): string
    {
        $url = 'https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=' . urlencode($payload);
        
        try {
            $imageData = file_get_contents($url);
            if ($imageData === false) {
                return '';
            }
            return 'data:image/png;base64,' . base64_encode($imageData);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Erro ao gerar QR Code PIX: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Mascara a chave PIX para exibição segura
     */
    protected function mascararChave(string $chave): string
    {
        $len = strlen($chave);
        if ($len <= 6) {
            return $chave;
        }

        $visivel = 3;
        return substr($chave, 0, $visivel) . str_repeat('*', $len - ($visivel * 2)) . substr($chave, -$visivel);
    }

    /**
     * Verifica se a loja tem PIX configurado
     */
    public function lojaTemPixConfigurado(int $lojaId): bool
    {
        $config = $this->configRepository->getPixConfigByLojaWithFallback($lojaId);
        return !empty($config['chave']);
    }
}
