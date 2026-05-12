<?php

namespace App\Http\Controllers;

use App\Helpers\PrinterHelper;
use App\Models\ServiceOrder;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    public function printReceipt(ServiceOrder $order)
    {
        $result = PrinterHelper::printReceipt($order);
        
        if ($result['success']) {
            return back()->with('success', $result['message']);
        }
        
        return back()->with('error', $result['message']);
    }
    
    public function testPrinter()
    {
        $result = PrinterHelper::testConnection();
        
        if ($result['success']) {
            return back()->with('success', $result['message']);
        }
        
        return back()->with('error', $result['message']);
    }
    
    // Versão AJAX
    public function printAjax(ServiceOrder $order)
    {
        $result = PrinterHelper::printReceipt($order);
        return response()->json($result);
    }
}