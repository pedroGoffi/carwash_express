<?php

namespace App\Helpers;

use App\Models\Order;

class PrinterHelper
{
    public static function printReceipt(Order $order)
    {
        try {
            // Comandos ESC/POS raw
            $commands = "";
            
            // Inicializar impressora
            $commands .= "\x1B\x40"; // ESC @
            
            // Centralizar
            $commands .= "\x1B\x61\x01"; // ESC a 1
            
            // Texto normal
            $commands .= "CAR WASH LAVAGEM LTDA\n";
            $commands .= "Rua Independência, 60 - Centro\n";
            $commands .= "CNPJ: 53.863.307/0001-82\n";
            $commands .= "Tel: (54) 99638-0894\n";
            $commands .= "-----------------------------\n";
            $commands .= "*** REIMPRESSÃO ***\n";
            $commands .= "-----------------------------\n";
            
            // Texto grande (dobrado)
            $commands .= "\x1D\x21\x11"; // Texto grande
            $commands .= $order->code . "\n";
            $commands .= "\x1D\x21\x00"; // Voltar normal
            
            $commands .= $order->license_plate . "\n";
            $commands .= "-----------------------------\n";
            
            // Alinhar esquerda
            $commands .= "\x1B\x61\x00"; // ESC a 0
            
            $permanencia = $order->created_at->diff($order->updated_at);
            $commands .= "Cliente: " . ($order->customer_name ?? 'NÃO IDENTIFICADO') . "\n";
            $commands .= "Entrada: " . $order->created_at->format('d/m/Y H:i:s') . "\n";
            $commands .= "Saída: " . $order->updated_at->format('d/m/Y H:i:s') . "\n";
            $commands .= "Permanência: " . sprintf('%02d:%02d:%02d', $permanencia->h, $permanencia->i, $permanencia->s) . "\n";
            $commands .= "-----------------------------\n";
            
            // Serviços
            $commands .= "SERVIÇOS\n";
            foreach ($order->products as $index => $item) {
                $commands .= sprintf("%02d", $index + 1) . " " . $item->name . "\n";
                $commands .= str_repeat(" ", 30) . "R$ " . number_format($item->pivot->unit_price, 2, ',', '.') . "\n";
            }
            
            $commands .= "-----------------------------\n";
            
            // Total à direita
            $commands .= "\x1B\x61\x02"; // ESC a 2 (direita)
            $commands .= "TOTAL: R$ " . number_format($order->total, 2, ',', '.') . "\n";
            
            // Centralizar novamente
            $commands .= "\x1B\x61\x01"; // ESC a 1
            $commands .= "-----------------------------\n";
            $commands .= now()->format('d/m/Y H:i:s') . "\n";
            $commands .= "MerchOnline - Pedro Goffi\n";
            $commands .= "• • • • • • • • • • • • • •\n";
            
            // Cortar papel
            $commands .= "\x1D\x56\x00"; // Cut paper
            
            // Enviar para impressora via porta paralela/USB
            $handle = fopen("LPT1", "wb");
            if (!$handle) {
                $handle = fopen("USB001", "wb");
            }
            if (!$handle) {
                $handle = fopen("COM3", "wb");
            }
            
            if ($handle) {
                fwrite($handle, $commands);
                fclose($handle);
                return ['success' => true, 'message' => 'Impresso com sucesso!'];
            } else {
                throw new \Exception("Não foi possível abrir a porta da impressora");
            }
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }
}