<?php
/**
 * Project codeigniter-basic-helper
 * Created by PhpStorm
 * User: 713uk13m <dev@nguyenanhung.com>
 * Copyright: 713uk13m <dev@nguyenanhung.com>
 * Date: 08/07/2021
 * Time: 01:07
 */

namespace nguyenanhung\CodeIgniter\BasicHelper;

use Exception;

/**
 * Class ImageHelper
 *
 * @package   nguyenanhung\CodeIgniter\BasicHelper
 * @author    713uk13m <dev@nguyenanhung.com>
 * @copyright 713uk13m <dev@nguyenanhung.com>
 */
class ImageHelper extends BaseHelper
{
    /**
     * Function googleGadgetsProxy
     *
     * @param string   $url
     * @param int      $width
     * @param null|int $height
     *
     * @return string
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 08/20/2021 11:20
     */
    public static function googleGadgetsProxy($url = '', $width = 100, $height = null)
    {
        $proxyUrl = 'https://images1-focus-opensocial.googleusercontent.com/gadgets/proxy';
        $proxyContainer = 'focus';
        $proxyRefresh = 2592000;
        $params = array();
        $params['url'] = $url;
        $params['resize_w'] = $width;
        if ($height !== null) {
            $params['resize_h'] = $height;
        }
        $params['container'] = $proxyContainer;
        $params['refresh'] = $proxyRefresh;
        $url = $proxyUrl . '?' . urldecode(http_build_query($params));
        return trim($url);
    }

    /**
     * Function googleGadgetsProxyDnsPrefetch
     *
     * @return string
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 09/15/2021 34:32
     */
    public static function googleGadgetsProxyDnsPrefetch()
    {
        return "<link href='//images1-focus-opensocial.googleusercontent.com' rel='dns-prefetch' />" . PHP_EOL;
    }

    /**
     * Function wordpressProxy
     *
     * @param string $imageUrl
     * @param string $server
     *
     * @return string
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 08/20/2021 11:39
     */
    public static function wordpressProxy($imageUrl = '', $server = 'i3')
    {
        $imageUrl = str_replace(array('https://', 'http://', '//'), '', $imageUrl);
        $url = 'https://' . trim($server) . '.wp.com/' . $imageUrl;
        return trim($url);
    }

    /**
     * Function wordpressProxyDnsPrefetch
     *
     * @return string
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 09/15/2021 33:45
     */
    public static function wordpressProxyDnsPrefetch()
    {
        $html = "<link href='//i0.wp.com' rel='dns-prefetch' />" . PHP_EOL;
        $html .= "<link href='//i1.wp.com' rel='dns-prefetch' />" . PHP_EOL;
        $html .= "<link href='//i2.wp.com' rel='dns-prefetch' />" . PHP_EOL;
        $html .= "<link href='//i3.wp.com' rel='dns-prefetch' />" . PHP_EOL;
        return $html;
    }

    /**
     * Function createThumbnail - Only use for CodeIgniter
     *
     * @param $url
     * @param $width
     * @param $height
     *
     * @return mixed|string|null
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 15/06/2022 00:20
     */
    public static function createThumbnail($url = '', $width = 100, $height = 100)
    {
        try {
            if (function_exists('base_url') && function_exists('config_item') && class_exists('nguyenanhung\MyImage\ImageCache')) {
                $tmpPath = config_item('image_tmp_path');
                $storagePath = config_item('base_storage_path');
                $cache = new \nguyenanhung\MyImage\ImageCache();
                $cache->setTmpPath($tmpPath);
                $cache->setUrlPath(base_url($storagePath));
                $cache->setDefaultImage();
                $thumbnail = $cache->thumbnail($url, $width, $height);
                if (!empty($thumbnail)) {
                    return $thumbnail;
                }
                return $cache->thumbnail(config_item('image_path_tmp_default'), $width, $height);
            }
            return $url;
        } catch (Exception $e) {
            if (function_exists('log_message')) {
                log_message('error', __get_error_message__($e));
            }
            return $url;
        }
    }

    /**
     * Function createThumbnailWithCodeIgniterCache
     *
     * @param $url
     * @param $width
     * @param $height
     *
     * @return mixed|string
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 25/02/2023 27:41
     */
    public static function createThumbnailWithCodeIgniterCache($url = '', $width = 100, $height = 100)
    {
        try {
            if (function_exists('base_url') && function_exists('config_item')) {
                $cacheKey = md5('createThumbnailWithCodeIgniterCache' . $url . $width . $height);
                $cacheTtl = 15552000; // Cache 6 tháng
                // Setup CodeIgniter
                $CI =& get_instance();
                $CI->load->driver('cache', array('adapter' => 'file', 'backup' => 'dummy'));
                if (!$urlThumbnail = $CI->cache->get($cacheKey)) {
                    $tmpPath = config_item('image_tmp_path');
                    $storagePath = config_item('base_storage_path');
                    $imageCache = new \nguyenanhung\MyImage\ImageCache();
                    $imageCache->setTmpPath($tmpPath);
                    $imageCache->setUrlPath(base_url($storagePath));
                    $imageCache->setDefaultImage();
                    $thumbnail = $imageCache->thumbnail($url, $width, $height);
                    if (!empty($thumbnail)) {
                        $urlThumbnail = $thumbnail;
                    } else {
                        $thumbnailTmp = $imageCache->thumbnail(config_item('image_path_tmp_default'), $width, $height);
                        $urlThumbnail = $thumbnailTmp;
                    }
                    if ($urlThumbnail !== null) {
                        $CI->cache->save($cacheKey, $urlThumbnail, $cacheTtl);
                    }
                }
                if (!empty($urlThumbnail)) {
                    return $urlThumbnail;
                }
                return $url;
            }
            return $url;
        } catch (Exception $e) {
            if (function_exists('log_message')) {
                log_message('error', __get_error_message__($e));
            }
            return $url;
        }
    }
}
