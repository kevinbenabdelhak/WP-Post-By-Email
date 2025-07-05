<?php 


if (!defined('ABSPATH')) exit;


// -------- 3. Parsing MIME (pièces jointes/images)
function pbe_parse_parts($structure, $inbox, $email_number, &$attachments, &$body, &$content_type, &$inline_cids, $prefix = '') {
    if (isset($structure->parts) && count($structure->parts)) {
        foreach ($structure->parts as $i => $part) {
            $part_number = ($prefix === '') ? ($i + 1) : ($prefix . '.' . ($i + 1));
            if (isset($part->parts) && count($part->parts)) {
                pbe_parse_parts($part, $inbox, $email_number, $attachments, $body, $content_type, $inline_cids, $part_number);
            } else {
                $part_body = imap_fetchbody($inbox, $email_number, $part_number);
                if ($part->encoding == 3) $part_body = base64_decode($part_body);
                elseif ($part->encoding == 4) $part_body = quoted_printable_decode($part_body);

                $filename = '';
                if (isset($part->dparameters)) {
                    foreach ($part->dparameters as $object) {
                        if(strtolower($object->attribute) == 'filename') $filename = $object->value;
                    }
                }
                if (empty($filename) && isset($part->parameters)) {
                    foreach ($part->parameters as $object) {
                        if(strtolower($object->attribute) == 'name') $filename = $object->value;
                    }
                }
                if ($part->type == 0) { // text
                    if (strtolower($part->subtype) == 'plain') {
                        if ($body == '' && empty($content_type)) {
                            $body = $part_body;
                            $content_type = 'plain';
                        }
                    } elseif (strtolower($part->subtype) == 'html') {
                        if ($body == '' || $content_type == 'plain') {
                            $body = $part_body;
                            $content_type = 'html';
                        }
                    }
                }
                // IMAGE INLINE DANS HTML
                elseif (
                    (!empty($filename) && preg_match('/\.(jpg|jpeg|png|gif|bmp|webp|svg)$/i', $filename)) &&
                    isset($part->disposition) && strtolower($part->disposition) == 'inline'
                ) {
                    $cid = isset($part->id) ? trim($part->id, '<>') : '';
                    $inline_cids[] = array(
                        'data' => $part_body,
                        'filename' => $filename,
                        'cid' => $cid,
                        'mimetype' => ($part->ifsubtype ? strtolower($part->subtype) : '')
                    );
                }
                // IMAGE PIECE-JOINTE (PAS INLINE) → pour la une
                elseif (
                    (!empty($filename) && preg_match('/\.(jpg|jpeg|png|gif|bmp|webp|svg)$/i', $filename)) &&
                    ( !isset($part->disposition) || strtolower($part->disposition) == 'attachment' )
                ) {
                    $cid = isset($part->id) ? trim($part->id, '<>') : '';
                    $attachments[] = array(
                        'data' => $part_body,
                        'filename' => $filename,
                        'cid' => $cid,
                        'mimetype' => ($part->ifsubtype ? strtolower($part->subtype) : '')
                    );
                }
                // Autre fichiers non images : ignore
            }
        }
    }
}