<?php
namespace Mouf\Utils\Graphics\MoufImagine\Controller;

use Imagine\Filter\FilterInterface;
use Imagine\Image\AbstractImagine;
use Mouf\MoufManager;
use Mouf\Mvc\Splash\Controllers\Controller;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Mouf\Mvc\Splash\Annotations\URL;

class ImagePresetController extends Controller{

    /**
     * @var string $url the base URL for generated images
     */
    private $url;

    /**
     * @var AbstractImagine
     */
    private $imagine;

    /**
     * @var string the folder where the original images are stored
     */
    private $originalPath;

    /**
     * @var FilterInterface[] a set of FilterInterface that will be applyied to the original image
     */
    private $filters;

    /**
     * The path to the image that should be displayed if the original image is not found
     * @var string
     */
    private $image404;

    /**
     * The Imagine Save options
     * @var array
     */
    private $imageSaveOptions = [];

    /**
     * Transform GIF as a single image
     * @var bool
     */
    private $transformGif;

    /**
     * Enable the application of the the preset when performing createPresets
     * @var bool
     */
    private $createPresetsEnabled;

    private static $formats = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_GIF  => 'gif',
        IMAGETYPE_WBMP => 'wbmp',
        IMAGETYPE_XBM => 'xbm'
    ];

    /**
     * @param string $url
     * @param string $originalPath
     * @param AbstractImagine $imagine
     * @param FilterInterface[] $filters
     * @param bool $transformGif Transform GIF as a single image
     * @param bool $createPresetsEnabled Enable the preset application when performing createPresets (default: true)
     */
    public function __construct($url, $originalPath, AbstractImagine $imagine, $filters, $transformGif = true,
                                $createPresetsEnabled = true){
        $this->url = $url;
        $this->originalPath = $originalPath;
        $this->imagine = $imagine;
        $this->filters = $filters;
        $this->transformGif = $transformGif;
        $this->createPresetsEnabled = $createPresetsEnabled;
    }

    /**
     * @param $imagePath
     * @return HtmlResponse|RedirectResponse
     */
    private function image($imagePath){
        $basePath = empty($this->originalPath) ? "" : ($this->originalPath . DIRECTORY_SEPARATOR);
        $originalImagePath = ROOT_PATH . $basePath . $imagePath;

        if (!file_exists($originalImagePath)){
            $defaultImagePath = ROOT_PATH.$this->image404;
            $extension = pathinfo($defaultImagePath, PATHINFO_EXTENSION);
            $finalPath = ROOT_PATH . $this->url . DIRECTORY_SEPARATOR . "default.".$extension;

            if (!file_exists($finalPath)) {
                $image = $this->generateImage($defaultImagePath, $finalPath);
            }

            return new RedirectResponse(ROOT_URL . $this->url . DIRECTORY_SEPARATOR . "default.".$extension);
        } else {
            $finalPath = ROOT_PATH . $this->url . DIRECTORY_SEPARATOR . $imagePath;

            $image = $this->generateImage($originalImagePath, $finalPath);

            $format = self::$formats[exif_imagetype($finalPath)];

            $html = $image->get($format);
            header('Content-type: '.$this->getMimeType($format));
            return new HtmlResponse($html, 200, ['Content-type' => $this->getMimeType($format)]);
        }
    }

    /**
     * @param $src
     * @param $dest
     * @return \Imagine\Image\ImageInterface
     * @throws \Exception
     */
    private function generateImage ($src, $dest) {
        $src = str_replace(['/', '\\'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $src);
        $dest = str_replace(['/', '\\'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $dest);

        $subPath = substr($dest, 0, strrpos($dest, DIRECTORY_SEPARATOR));
        if (!file_exists($subPath)){
            $oldUmask = umask();
            umask(0);
            $dirCreate = mkdir($subPath, 0775, true);
            umask($oldUmask);
            if (!$dirCreate) {
                throw new \Exception("Could't create subfolders '$subPath' in " . $dest);
            }
        }

        $image = $this->imagine->open($src);
        $extension = pathinfo($src, PATHINFO_EXTENSION);
        if ($extension == 'gif') {
            if ($this->transformGif == true) {
                $layers = $image->layers();
                $layer = $layers->get(0);
                foreach ($this->filters as $filter){
                    $layer = $filter->apply($layer);
                }
                $layer->save($dest, $this->imageSaveOptions);
            } else {
                $image->layers()->coalesce();
                $layers = $image->layers();
                foreach ($layers as $layer) {
                    foreach ($this->filters as $filter){
                        $layer = $filter->apply($layer);
                    }
                }
                $image->save($dest, array_merge(array(
                    'animated' => true,
                ), $this->imageSaveOptions));
            }
        } else {
            foreach ($this->filters as $filter){
                $image = $filter->apply($image);
            }
            $image->save($dest, $this->imageSaveOptions);
        }
        return $image;
    }

    /**
     * Delete a preset of an image
     * @param $imagePath
     */
    private function deletePreset ($imagePath) {
        $finalPath = ROOT_PATH . $this->url . DIRECTORY_SEPARATOR . $imagePath;
        if (file_exists($finalPath)){
            unlink($finalPath);
        }
    }

    /**
     * Create presets of an image
     * @param string $path
     */
    public static function createPresets($path = null, $callback = null) {
        $moufManager = MoufManager::getMoufManager();
        $instances = $moufManager->findInstances('Mouf\\Utils\\Graphics\\MoufImagine\\Controller\\ImagePresetController');
        foreach ($instances as $instanceName) {
            $instance = $moufManager->getInstance($instanceName);
            if ($path && strpos($path, $instance->originalPath) !== false && $instance->createPresetsEnabled === true) {
                $imagePath = substr($path, strlen($instance->originalPath) + 1);
                $instance->image($imagePath);

                if(is_callable($callback)) {
                    $finalPath = ROOT_PATH . $instance->url . DIRECTORY_SEPARATOR . $imagePath;
                    $callback($finalPath);
                }
            }
        }
    }

    /**
     * Purge presets of an image
     * @param string $path
     */
    public static function purgePresets($path = null, $callback = null) {
        $moufManager = MoufManager::getMoufManager();
        $instances = $moufManager->findInstances('Mouf\\Utils\\Graphics\\MoufImagine\\Controller\\ImagePresetController');
        foreach ($instances as $instanceName) {
            $instance = $moufManager->getInstance($instanceName);
            if ($path && strpos($path, $instance->originalPath) !== false) {
                $imagePath = substr($path, strlen($instance->originalPath) + 1);
                $instance->deletePreset($imagePath);
                
                if(is_callable($callback)) {
                    $finalPath = ROOT_PATH . $instance->url . DIRECTORY_SEPARATOR . $imagePath;
                    $callback($finalPath);
                }
            }
        }
    }

    /**
     * @URL("{$this->url}/{image}")
     * @param string $image
     * @return HtmlResponse|RedirectResponse
     */
    public function baseImage($image){
        return $this->image($image);
    }

    /**
     * @URL("{$this->url}/{path1}/{image}")
     * @param string $image
     * @param string path1
     * @return HtmlResponse|RedirectResponse
     */
    public function imageLevel1($image, $path1){
        return $this->image("$path1/$image");
    }

    /**
     * @URL("{$this->url}/{path1}/{path2}/{image}")
     * @param string $image
     * @param string path1
     * @param string path2
     * @return HtmlResponse|RedirectResponse
     */
    public function imageLevel2($image, $path1, $path2){
        return $this->image("$path1/$path2/$image");
    }

    /**
     * @URL("{$this->url}/{path1}/{path2}/{path3}/{image}")
     * @param string $image
     * @param string path1
     * @param string path2
     * @param string path3
     * @return HtmlResponse|RedirectResponse
     */
    public function imageLevel3($image, $path1, $path2, $path3){
        return $this->image("$path1/$path2/$path3/$image");
    }

    /**
     * @URL("{$this->url}/{path1}/{path2}/{path3}/{path4}/{image}")
     * @param string $image
     * @param string path1
     * @param string path2
     * @param string path3
     * @param string path4
     * @return HtmlResponse|RedirectResponse
     */
    public function imageLevel4($image, $path1, $path2, $path3, $path4){
        return $this->image("$path1/$path2/$path3/$path4/$image");
    }

    /**
     * @URL("{$this->url}/{path1}/{path2}/{path3}/{path4}/{path5}/{image}")
     * @param string $image
     * @param string path1
     * @param string path2
     * @param string path3
     * @param string path4
     * @param string path5
     * @return HtmlResponse|RedirectResponse
     */
    public function imageLevel5($image, $path1, $path2, $path3, $path4, $path5){
        return $this->image("$path1/$path2/$path3/$path4/$path5/$image");
    }

    /**
     * @param string $image404
     */
    public function setImage404($image404)
    {
        $this->image404 = $image404;
    }

    /**
     * Internal
     *
     * Get the mime type based on format.
     *
     * @param string $format
     *
     * @return string mime-type
     *
     * @throws \RuntimeException
     */
    private function getMimeType($format)
    {
        $format = $this->normalizeFormat($format);

        static $mimeTypes = array(
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'png'  => 'image/png',
            'wbmp' => 'image/vnd.wap.wbmp',
            'xbm'  => 'image/xbm',
        );

        if (!isset($mimeTypes[$format])) {
            throw new \RuntimeException('Invalid format');
        }

        return $mimeTypes[$format];
    }

    /**
     * Internal
     *
     * Normalizes a given format name
     *
     * @param string $format
     *
     * @return string
     */
    private function normalizeFormat($format)
    {
        $format = strtolower($format);

        if ('jpg' === $format || 'pjpeg' === $format) {
            $format = 'jpeg';
        }

        return $format;
    }

    /**
     * @param array $imageSaveOptions
     */
    public function setImageSaveOptions($imageSaveOptions)
    {
        $this->imageSaveOptions = $imageSaveOptions;
    }

}
