<?php 


if (!defined('ABSPATH')) exit;


function pbe_check_and_create_posts() {
    $opts = get_option('pbe_mail_settings');
    if(empty($opts['email']) || empty($opts['password']) || empty($opts['host']) || empty($opts['port'])) return 0;
    if (!function_exists('imap_open')) return 0;

    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $mailbox = '{' . $opts['host'] . ':' . $opts['port'] . '/imap/ssl}INBOX';
    $inbox = @imap_open($mailbox, $opts['email'], $opts['password']);
    if (!$inbox) return 0;
    $emails = imap_search($inbox, 'UNSEEN');
    $count = 0;

    if($emails) {
        $allowed = array_map('trim', explode(',', $opts['allowed'] ?? '*'));
        foreach ($emails as $email_number) {
            $header = imap_headerinfo($inbox, $email_number);
            $from = isset($header->from[0]) ? strtolower($header->from[0]->mailbox.'@'.$header->from[0]->host) : '';
   if (isset($header->subject)) {
    $subject_raw = $header->subject;
    if (function_exists('mb_decode_mimeheader')) {
        $subject = mb_decode_mimeheader($subject_raw);
    } elseif (function_exists('iconv_mime_decode')) {
        $subject = iconv_mime_decode($subject_raw, 0, 'UTF-8');
    } else {
        $subject = imap_utf8($subject_raw);
    }
} else {
    $subject = '(Sans titre)';
}
            if ($opts['allowed'] !== '*' && !in_array($from, $allowed)) {
                imap_setflag_full($inbox, $email_number, "\\Seen");
                continue;
            }
            $structure = imap_fetchstructure($inbox, $email_number);
            $body = '';
            $content_type = '';
            $attachments = array();
            $inline_cids = array();
            $cid_to_url = array();
            pbe_parse_parts($structure, $inbox, $email_number, $attachments, $body, $content_type, $inline_cids, '');

            // Fallback vieux mails
            if ($body == '') {
                if (!empty($structure->parts)) {
                    $txt = imap_fetchbody($inbox, $email_number, 1);
                    if ($structure->parts[0]->encoding == 3) $txt = base64_decode($txt);
                    elseif ($structure->parts[0]->encoding == 4) $txt = quoted_printable_decode($txt);
                    $body = $txt;
                    $content_type = (isset($structure->parts[0]->subtype) && strtolower($structure->parts[0]->subtype) == 'html') ? 'html' : 'plain';
                } else {
                    $txt = imap_body($inbox, $email_number);
                    if ($structure->encoding == 3) $txt = base64_decode($txt);
                    elseif ($structure->encoding == 4) $txt = quoted_printable_decode($txt);
                    $body = $txt;
                    $content_type = (isset($structure->subtype) && strtolower($structure->subtype) == 'html') ? 'html' : 'plain';
                }
            }

            //  Upload 1ère pièce-jointe image trouvée (pour la une)
            $feat_img_id = 0;
            if (!empty($attachments)) {
                $att = $attachments[0];
                if (!empty($att['data'])) {
                    $file_ext = '';
                    if (!empty($att['filename']) && preg_match('/\.(jpg|jpeg|png|gif|bmp|webp|svg)$/i', $att['filename'], $m)) {
                        $file_ext = strtolower($m[1]);
                    } elseif (!empty($att['mimetype'])) {
                        $file_ext = strtolower($att['mimetype']);
                    }
                    if ($file_ext) {
                        $tmpfname = tempnam(sys_get_temp_dir(), 'pbeimg');
                        file_put_contents($tmpfname, $att['data']);
                        $file_array = array(
                            'name' => (empty($att['filename']) ? 'image_att_' . uniqid() . '.' . $file_ext : $att['filename']),
                            'tmp_name' => $tmpfname
                        );
                        $attachment_id = media_handle_sideload($file_array, 0);
                        @unlink($tmpfname);
                        if (!is_wp_error($attachment_id)) {
                            $feat_img_id = $attachment_id;
                        }
                    }
                }
            }

            // Upload images inline HTML
            if (!empty($inline_cids)) {
                foreach ($inline_cids as $inline) {
                    if (empty($inline['data'])) continue;
                    $file_ext = '';
                    if (!empty($inline['filename']) && preg_match('/\.(jpg|jpeg|png|gif|bmp|webp|svg)$/i', $inline['filename'], $m)) {
                        $file_ext = strtolower($m[1]);
                    } elseif (!empty($inline['mimetype'])) {
                        $file_ext = strtolower($inline['mimetype']);
                    }
                    if (!$file_ext) continue;
                    $tmpfname = tempnam(sys_get_temp_dir(), 'pbeimg');
                    file_put_contents($tmpfname, $inline['data']);
                    $file_array = array(
                        'name' => (empty($inline['filename']) ? 'image_inline_' . uniqid() . '.' . $file_ext : $inline['filename']),
                        'tmp_name' => $tmpfname
                    );
                    $attachment_id = media_handle_sideload($file_array, 0);
                    @unlink($tmpfname);
                    if (!is_wp_error($attachment_id)) {
                        $url = wp_get_attachment_url($attachment_id);
                        if (!empty($inline['cid'])) {
                            $cid_to_url[$inline['cid']] = $url;
                        }
                    }
                }
            }

            //  Remplacement des src=cid:xxx
            if (!empty($cid_to_url) && $content_type == 'html') {
                $body = preg_replace_callback('/src=["\']cid:([^"\']+)["\']/', function($matches) use ($cid_to_url) {
                    $cid = $matches[1];
                    if (isset($cid_to_url[$cid])) {
                        return 'src="' . esc_url($cid_to_url[$cid]) . '"';
                    }
                    return $matches[0];
                }, $body);
            }

            // Nettoie début et extrait <body>, évite les retours à la ligne en trop
          if ($content_type == 'html') {
  
    $body = preg_replace('/<\s*\/?\s*(html|head|body)[^>]*>/i', '', $body);

    $fragments = preg_split('/(<img[^>]+>)/i', $body, -1, PREG_SPLIT_DELIM_CAPTURE);

    $output = '';
    foreach ($fragments as $frag) {
        $frag = trim($frag);
        if ($frag === '') continue;
        if (preg_match('/^<img[^>]+>$/i', $frag)) {
        
            $output .= $frag;
        } else {
         
            $frag = preg_replace('/<\s*\/?\s*p[^>]*>/i', '', $frag);
            $frag = preg_replace('/<br\s*\/?>/i', '', $frag);
            $frag = trim($frag);
            if ($frag !== '') {
                $frag = preg_replace('/\s+/u', ' ', $frag); // linéarise
                $output .= '<p>' . $frag . '</p>';
            }
        }
    }
    $body = $output;
} else {
 
    $body = trim($body);
    $body = str_replace(["\r\n", "\r"], "\n", $body);
    $body = preg_replace('/([^\n])\n([^\n])/m', '$1 $2', $body);
    $body = preg_replace('/([^\n])\n([^\n])/m', '$1 $2', $body);
    $body = preg_replace("/\n{3,}/", "\n\n", $body);
    $body = esc_html($body);
    $body = wpautop($body);
}
            $subject = str_replace('_', ' ', $subject);


            $post = array(
                'post_title'    => $subject,
                'post_content'  => $body,
                'post_status'   => 'publish',
                'post_author'   => 1
            );
            $post_id = wp_insert_post($post);


            // Mettre la une si dispo
            if ($feat_img_id && $post_id && is_numeric($post_id)) {
                set_post_thumbnail($post_id, $feat_img_id);
                wp_update_post(array(
                    'ID' => $feat_img_id,
                    'post_parent' => $post_id
                ));
            }

            $count++;
            imap_setflag_full($inbox, $email_number, "\\Seen");
        }
    }
    imap_close($inbox);
    return $count;
}