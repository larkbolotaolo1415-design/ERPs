<?php

require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/MPDF/vendor/autoload.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];
$template = isset($data['template']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $data['template']) : '';
$filename = isset($data['filename']) ? preg_replace('/[^a-zA-Z0-9_\-.]/', '', $data['filename']) : 'document.pdf';
$return_html = !empty($data['return_html']);

function render_template($name, $data)
{
    $path = MODULES_PATH . '/pdf_templates/' . $name . '.html';
    if (is_file($path)) {
        $html = file_get_contents($path);
        foreach ($data as $k => $v) {
            if (is_scalar($v)) {
                $html = str_replace('{{' . $k . '}}', htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'), $html);
            }
        }
        return $html;
    }
    return '';
}

function render_payroll_table($payload)
{
    $title = isset($payload['title']) ? $payload['title'] : 'Payrolls';
    $headers = is_array($payload['headers'] ?? null) ? $payload['headers'] : [];
    $rows = is_array($payload['rows'] ?? null) ? $payload['rows'] : [];
    $h = '<h2 style="margin:0 0 12px 0;">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>';
    $h .= '<table border="1" cellspacing="0" cellpadding="6" style="border-collapse:collapse;width:100%;font-size:12px;">';
    if ($headers) {
        $h .= '<thead><tr>';
        foreach ($headers as $c) {
            $h .= '<th style="background:#f0f0f0;">' . htmlspecialchars((string)$c, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        $h .= '</tr></thead>';
    }
    $h .= '<tbody>';
    foreach ($rows as $r) {
        $h .= '<tr>';
        foreach ($r as $c) {
            $h .= '<td>' . htmlspecialchars((string)$c, ENT_QUOTES, 'UTF-8') . '</td>';
        }
        $h .= '</tr>';
    }
    $h .= '</tbody></table>';
    return $h;
}

// Batch support: compile multiple pages into single PDF or ZIP of PDFs
if (isset($data['pages']) && is_array($data['pages']) && count($data['pages']) > 0) {
    $pages = $data['pages'];
    $zipOut = !empty($data['zip']);
    if ($zipOut) {
        $zip = new \ZipArchive();
        $tmpZip = tempnam(sys_get_temp_dir(), 'pslip_zip_');
        $zip->open($tmpZip, \ZipArchive::OVERWRITE);
        foreach ($pages as $pg) {
            $tpl = preg_replace('/[^a-zA-Z0-9_\-]/', '', $pg['template'] ?? 'payslip');
            $dat = is_array($pg['data'] ?? null) ? $pg['data'] : [];
            $fname = preg_replace('/[^a-zA-Z0-9_\-.]/', '', $pg['filename'] ?? ('payslip_' . uniqid() . '.pdf'));
            $html = render_template($tpl, $dat);
            $mp = new \Mpdf\Mpdf();
            $mp->WriteHTML($html ?: '<div>Empty</div>');
            $pdfContent = $mp->Output('', \Mpdf\Output\Destination::STRING_RETURN);
            $zip->addFromString($fname, $pdfContent);
        }
        $zip->close();
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . ($filename ?: 'payslips.zip'));
        readfile($tmpZip);
        @unlink($tmpZip);
        exit;
    } else {
        $mpdf = new \Mpdf\Mpdf();
        $first = true;
        foreach ($pages as $pg) {
            $tpl = preg_replace('/[^a-zA-Z0-9_\-]/', '', $pg['template'] ?? 'payslip');
            $dat = is_array($pg['data'] ?? null) ? $pg['data'] : [];
            $html = render_template($tpl, $dat);
            if (!$first) {
                $mpdf->WriteHTML('<pagebreak />');
            }
            $mpdf->WriteHTML($html ?: '<div>Empty</div>');
            $first = false;
        }
        $pdfContent = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename=' . $filename);
        echo $pdfContent;
        exit;
    }
}

// Single template
$html = '';
if ($template) {
    $html = render_template($template, $data);
}
if ($html === '' && $template === 'payroll_table') {
    $html = render_payroll_table($data);
}
if ($html === '') {
    $html = '<div>Empty</div>';
}

if ($return_html) {
    header('Content-Type: text/html; charset=UTF-8');
    echo $html;
    exit;
}

$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);
$pdfContent = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename=' . $filename);
echo $pdfContent;
