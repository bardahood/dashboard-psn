<?php

namespace App\Support;

use DOMDocument;
use DOMXPath;
use ZipArchive;

/**
 * Parser generik untuk membaca paragraf & tabel dari file .docx (mis. lampiran
 * "Daftar PSN dalam RKP") tanpa dependensi pustaka pihak ketiga -- cukup unzip
 * lalu baca word/document.xml dengan DOMDocument/XPath.
 *
 * Sel yang di-merge horizontal (w:gridSpan) di Word disimpan sebagai SATU
 * <w:tc>, sehingga teksnya diduplikasi sebanyak gridSpan agar jumlah kolom
 * hasil parse konsisten per baris (meniru perilaku python-docx `row.cells`).
 */
class DocxTableParser
{
    private const NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    /**
     * @return array<int, array{type: string, text?: string, rows?: array<int, array<int, string>>}>
     *         Urutan blok dokumen: ['type' => 'paragraph', 'text' => ...] atau ['type' => 'table', 'rows' => [[cell, cell, ...], ...]]
     */
    public function parseBlocks(string $path): array
    {
        $xml = $this->readDocumentXml($path);

        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', self::NS);

        $blocks = [];

        foreach ($xpath->query('/w:document/w:body/*') as $node) {
            if ($node->localName === 'p') {
                $blocks[] = ['type' => 'paragraph', 'text' => $this->paragraphText($xpath, $node)];
            } elseif ($node->localName === 'tbl') {
                $blocks[] = ['type' => 'table', 'rows' => $this->tableRows($xpath, $node)];
            }
        }

        return $blocks;
    }

    private function readDocumentXml(string $path): string
    {
        $zip = new ZipArchive();
        $zip->open($path);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        return $xml ?: '';
    }

    private function paragraphText(DOMXPath $xpath, \DOMNode $paragraph): string
    {
        $texts = [];
        foreach ($xpath->query('.//w:t', $paragraph) as $t) {
            $texts[] = $t->textContent;
        }

        return implode('', $texts);
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function tableRows(DOMXPath $xpath, \DOMNode $table): array
    {
        $rows = [];

        foreach ($xpath->query('./w:tr', $table) as $tr) {
            $cells = [];
            foreach ($xpath->query('./w:tc', $tr) as $tc) {
                $paragraphs = [];
                foreach ($xpath->query('./w:p', $tc) as $p) {
                    $paragraphs[] = $this->paragraphText($xpath, $p);
                }
                $text = trim(implode("\n", $paragraphs));
                $span = (int) ($xpath->query('.//w:tcPr/w:gridSpan/@w:val', $tc)->item(0)?->nodeValue ?? 1);
                $span = max(1, $span);
                for ($i = 0; $i < $span; $i++) {
                    $cells[] = $text;
                }
            }
            $rows[] = $cells;
        }

        return $rows;
    }
}
