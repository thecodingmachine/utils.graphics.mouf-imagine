<?php
namespace Mouf\Utils\Graphics\MoufImagine\Controller;

use Imagine\Filter\FilterInterface;
use Imagine\Image\AbstractImagine;
use Imagine\Imagick\Imagine;
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Mvc\Splash\UrlEntryPoint;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
     */
    public function __construct($url, $originalPath, AbstractImagine $imagine, $filters){
        $this->url = $url;
        $this->originalPath = $originalPath;
        $this->imagine = $imagine;
        $this->filters = $filters;
    }

    private function image($imagePath){
        $basePath = empty($this->originalPath) ? "" : ($this->originalPath . DIRECTORY_SEPARATOR);
        $originalImagePath = ROOT_PATH . $basePath . $imagePath;


        if (!file_exists($originalImagePath)){
            $defaultImagePath = ROOT_PATH.$this->image404;
            $extension = pathinfo($defaultImagePath, PATHINFO_EXTENSION);
            $finalPath = ROOT_PATH . $this->url . DIRECTORY_SEPARATOR . "default.".$extension;
            $subPath = substr($finalPath, 0, strrpos($finalPath, DIRECTORY_SEPARATOR));

            if (!file_exists($finalPath)) {
                $image = $this->imagine->open($defaultImagePath);
                foreach ($this->filters as $filter){
                    $image = $filter->apply($image);
                }

                if (!file_exists($subPath)){
                    $oldUmask = umask();
                    umask(0);
                    $dirCreate = mkdir($subPath, 0775, true);
                    umask($oldUmask);
                    if (!$dirCreate) {
                        throw new \Exception("Could't create subfolders '$subPath' in " . $finalPath);
                    }
                }

                $image->save($finalPath);
            }

            return new RedirectResponse(ROOT_URL . $this->url . DIRECTORY_SEPARATOR . "default.".$extension);
        } else {
            $image = $this->imagine->open($originalImagePath);
            foreach ($this->filters as $filter){
                $image = $filter->apply($image);
            }

            $finalPath = ROOT_PATH . $this->url . DIRECTORY_SEPARATOR . $imagePath;
            $subPath = substr($finalPath, 0, strrpos($finalPath, DIRECTORY_SEPARATOR));

            if (!file_exists($subPath)){
                $oldUmask = umask();
                umask(0);
                $dirCreate = mkdir($subPath, 0775, true);
                umask($oldUmask);
                if (!$dirCreate) {
                    throw new \Exception("Could't create subfolders '$subPath' in " . ROOT_PATH . $this->getSavePath());
                }
            }

            $image->save($finalPath);

            $format = self::$formats[exif_imagetype($finalPath)];

            return $image->show($format);
        }
    }

    /**
     * @URL {$this->url}/{image}
     * @param string $image
     */
    public function baseImage($image){
        return $this->image($image);
    }

    /**
     * @URL {$this->url}/{path1}/{image}
     * @param string $image
     * @param string path1
     */
    public function imageLevel1($image, $path1){
        return $this->image("$path1/$image");
    }

    /**
     * @URL {$this->url}/{path1}/{path2}/{image}
     * @param string $image
     * @param string path1
     * @param string path2
     */
    public function imageLevel2($image, $path1, $path2){
        return $this->image("$path1/$path2/$image");
    }

    /**
     * @URL {$this->url}/{path1}/{path2}/{path3}/{image}
     * @param string $image
     * @param string path1
     * @param string path2
     * @param string path3
     */
    public function imageLevel3($image, $path1, $path2, $path3){
        return $this->image("$path1/$path2/$path3/$image");
    }

    /**
     * @URL {$this->url}/{path1}/{path2}/{path3}/{path4}/{image}
     * @param string $image
     * @param string path1
     * @param string path2
     * @param string path3
     * @param string path4
     */
    public function imageLevel4($image, $path1, $path2, $path3, $path4){
        return $this->image("$path1/$path2/$path3/$path4/$image");
    }

    /**
     * @URL {$this->url}/{path1}/{path2}/{path3}/{path4}/{path5}/{image}
     * @param string $image
     * @param string path1
     * @param string path2
     * @param string path3
     * @param string path4
     * @param string path5
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



}
