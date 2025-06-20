<?php

namespace App\Service;

use App\Entity\Order;
use Dompdf\Dompdf;
use Twig\Environment;

class InvoiceGenerator
{
    private $dompdf;
    private $twig;

    public function __construct(Dompdf $dompdf, Environment $twig)
    {
        $this->dompdf = $dompdf;
        $this->twig = $twig;
    }

    public function generateInvoice(Order $order): string
    {
        $html = $this->twig->render('billing/invoice.html.twig', ['order' => $order]);
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();

        $output = $this->dompdf->output();
        $filePath = sprintf('%s/invoice_%d.pdf', sys_get_temp_dir(), $order->getId());
        file_put_contents($filePath, $output);

        return $filePath;
    }
}