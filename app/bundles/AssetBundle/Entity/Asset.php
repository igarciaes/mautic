<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\AssetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Entity\FormEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class Asset
 * @ORM\Table(name="assets")
 * @ORM\Entity(repositoryClass="Mautic\AssetBundle\Entity\AssetRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class Asset extends FormEntity
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"assetDetails", "assetList"})
     */
    private $id;

    /**
     * @ORM\Column(name="title", type="string")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"assetDetails", "assetList"})
     */
    private $title;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"assetDetails"})
     */
    private $description;

    /**
     * @ORM\Column(name="path", type="string", nullable=true)
     */
    private $path;

    /**
     * @ORM\Column(name="original_file_name", type="string", nullable=true)
     */
    private $originalFileName;

    /**
     * @Assert\File(maxSize="6000000")
     */
    private $file;

    /**
     * Holds upload directory
     */
    private $uploadDir;

    /**
     * Holds file type (file extension)
     */
    private $fileType;

    /**
     * Temporary location when asset file is beeing updated.
     * We need to keep the old file till we are sure the new
     * one is stored correctly.
     */
    private $temp;

    /**
     * @ORM\Column(name="alias", type="string")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"assetDetails", "assetList"})
     */
    private $alias;

    /**
     * @ORM\Column(name="lang", type="string")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"assetDetails"})
     */
    private $language = 'en';

    /**
     * @ORM\Column(name="publish_up", type="datetime", nullable=true)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"assetDetails"})
     */
    private $publishUp;

    /**
     * @ORM\Column(name="publish_down", type="datetime", nullable=true)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"assetDetails"})
     */
    private $publishDown;

    /**
     * @ORM\Column(name="download_count", type="integer")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"assetDetails"})
     */
    private $downloadCount = 0;

    /**
     * @ORM\Column(name="unique_download_count", type="integer")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"assetDetails"})
     */
    private $uniqueDownloadCount = 0;

    /**
     * @ORM\Column(name="revision", type="integer")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"assetDetails"})
     */
    private $revision = 1;

    /**
     * @ORM\ManyToOne(targetEntity="Mautic\CategoryBundle\Entity\Category")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"assetDetails", "assetList"})
     **/
    private $category;

    public function __clone()
    {
        $this->id = null;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;

        // check if we have an old asset path
        if (isset($this->path)) {
            // store the old name to delete after the update
            $this->temp = $this->path;
            $this->path = null;
        }
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Asset
     */
    public function setTitle($title)
    {
        $this->isChanged('title', $title);
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set originalFileName
     *
     * @param string $originalFileName
     *
     * @return Asset
     */
    public function setOriginalFileName($originalFileName)
    {
        $this->isChanged('originalFileName', $originalFileName);
        $this->originalFileName = $originalFileName;

        return $this;
    }

    /**
     * Get originalFileName
     *
     * @return string
     */
    public function getOriginalFileName()
    {
        return $this->originalFileName;
    }

    /**
     * Set path
     *
     * @param string $path
     *
     * @return Asset
     */
    public function setPath($path)
    {
        $this->isChanged('path', $path);
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set alias
     *
     * @param string $alias
     *
     * @return Asset
     */
    public function setAlias($alias)
    {
        $this->isChanged('alias', $alias);
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set publishUp
     *
     * @param \DateTime $publishUp
     *
     * @return Asset
     */
    public function setPublishUp($publishUp)
    {
        $this->isChanged('publishUp', $publishUp);
        $this->publishUp = $publishUp;

        return $this;
    }

    /**
     * Get publishUp
     *
     * @return \DateTime
     */
    public function getPublishUp()
    {
        return $this->publishUp;
    }

    /**
     * Set publishDown
     *
     * @param \DateTime $publishDown
     *
     * @return Asset
     */
    public function setPublishDown($publishDown)
    {
        $this->isChanged('publishDown', $publishDown);
        $this->publishDown = $publishDown;

        return $this;
    }

    /**
     * Get publishDown
     *
     * @return \DateTime
     */
    public function getPublishDown()
    {
        return $this->publishDown;
    }

    /**
     * Set downloadCount
     *
     * @param integer $downloadCount
     *
     * @return Asset
     */
    public function setDownloadCount($downloadCount)
    {
        $this->downloadCount = $downloadCount;

        return $this;
    }

    /**
     * Get downloadCount
     *
     * @return integer
     */
    public function getDownloadCount()
    {
        return $this->downloadCount;
    }

    /**
     * Set revision
     *
     * @param integer $revision
     *
     * @return Asset
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;

        return $this;
    }

    /**
     * Get revision
     *
     * @return integer
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * Set language
     *
     * @param string $language
     *
     * @return Asset
     */
    public function setLanguage($language)
    {
        $this->isChanged('language', $language);
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set category
     *
     * @param \Mautic\CategoryBundle\Entity\Category $category
     *
     * @return Asset
     */
    public function setCategory(\Mautic\CategoryBundle\Entity\Category $category = null)
    {
        $this->isChanged('category', $category);
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Mautic\CategoryBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param $prop
     * @param $val
     */
    protected function isChanged($prop, $val)
    {
        $getter  = "get" . ucfirst($prop);
        $current = $this->$getter();

        parent::isChanged($prop, $val);
    }

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Set uniqueDownloadCount
     *
     * @param integer $uniqueDownloadCount
     *
     * @return Asset
     */
    public function setUniqueDownloadCount($uniqueDownloadCount)
    {
        $this->uniqueDownloadCount = $uniqueDownloadCount;

        return $this;
    }

    /**
     * Get uniqueDownloadCount
     *
     * @return integer
     */
    public function getUniqueDownloadCount()
    {
        return $this->uniqueDownloadCount;
    }

    public function preUpload()
    {
        if (null !== $this->file) {
            $this->setOriginalFileName($this->file->getClientOriginalName());

            // set the asset title as original file name if title is missing
            if (null === $this->getTitle()) {
                $this->setTitle($this->file->getClientOriginalName());
            }

            $filename   = sha1(uniqid(mt_rand(), true));
            $extension  = $this->getFile()->guessExtension();

            if (empty($extension)) {
                //get it from the original name
                $extension = pathinfo($this->originalFileName, PATHINFO_EXTENSION);
            }
            $this->path = $filename . '.' . $extension;
        }
    }

    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return;
        }

        // move takes the target directory and then the
        // target filename to move to
        $this->getFile()->move($this->getUploadDir(), $this->path);
        $filePath = $this->getUploadDir() . '/' . $this->temp;

        // check if we have an old asset
        if (isset($this->temp) && file_exists($filePath)) {
            // delete the old asset
            unlink($filePath);
            // clear the temp asset path
            $this->temp = null;
        }

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }

    public function removeUpload()
    {
        $file = $this->getAbsolutePath();
        if ($file && file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Returns absolute path to the file.
     *
     * @return string
     */
    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir() . '/' . $this->path;
    }

    /**
     * Returns absolute path to upload dir.
     *
     * @return string
     */
    protected function getUploadDir()
    {
        if ($this->uploadDir) {
            return $this->uploadDir;
        }

        return 'media/files';
    }

    /**
     * Set uploadDir
     *
     * @param string $uploadDir
     *
     * @return Asset
     */
    public function setUploadDir($uploadDir)
    {
        $this->uploadDir = $uploadDir;

        return $this;
    }

    /**
     * Returns file extension
     *
     * @return string
     */
    public function getFileType()
    {
        if ($this->loadFile() === null) {
            return '';
        }

        return $this->loadFile()->guessExtension();
    }

    /**
     * Returns file mime type
     *
     * @return string
     */
    public function getFileMimeType()
    {
        if ($this->loadFile() === null) {
            return '';
        }

        return $this->loadFile()->getMimeType();
    }

    /**
     * Returns file size in kB
     *
     * @return int
     */
    public function getFileSize()
    {
        if ($this->loadFile() === null) {
            return '';
        }

        return round($this->loadFile()->getSize() / 1000);
    }

    /**
     * Returns Font Awesome icon class based on file type.
     *
     * @return string
     */
    public function getIconClass()
    {
        $fileType = $this->getFileType();

        // return missing file icon if file type is empty
        if (!$fileType) {
            return 'fa fa-ban';
        }

        $fileTypes = $this->getFileExtensions();

        // Search for icon name by file extension.
        foreach ($fileTypes as $icon => $extensions) {
            if (in_array($fileType, $extensions)) {
                return 'fa fa-file-' . $icon . '-o';
            }
        }

        // File extension is unknown, display general file icon.
        return 'fa fa-file-o';
    }

    /**
     * Decides if an asset is image displayable by browser.
     *
     * @return boolean
     */
    public function isImage()
    {
        $fileType = strtolower($this->getFileType());

        if (!$fileType) {
            return false;
        }

        $imageTypes = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($fileType, $imageTypes)) {
            return true;
        }

        return false;
    }

    /**
     * Returns array of common extensions
     *
     * @return string
     */
    public function getFileExtensions()
    {
        return array(
            'excel' => array(
                'xlsx',
                'xlsm',
                'xlsb',
                'xltx',
                'xltm',
                'xls',
                'xlt'
                ),
            'word' => array(
                'doc',
                'docx',
                'docm',
                'dotx'
                ),
            'pdf' => array(
                'pdf'
                ),
            'audio' => array(
                'mp3'
                ),
            'archive' => array(
                'zip',
                'rar',
                'iso',
                'tar',
                'gz',
                '7z'
                ),
            'image' => array(
                'jpg',
                'jpeg',
                'png',
                'gif',
                'ico',
                'bmp',
                'psd'
                ),
            'text' => array(
                'txt',
                'pub'
                ),
            'code' => array(
                'php',
                'js',
                'json',
                'yaml',
                'xml',
                'html',
                'htm',
                'sql'
                ),
            'powerpoint' => array(
                'ppt',
                'pptx',
                'pptm',
                'xps',
                'potm',
                'potx',
                'pot',
                'pps',
                'odp'
                ),
            'video' => array(
                'wmv',
                'avi',
                'mp4',
                'mkv',
                'mpeg'
                )
            );
    }

    /**
     * Load the file object from it's path.
     *
     * @return null|\Symfony\Component\HttpFoundation\File\File
     */
    public function loadFile()
    {
        if (!$this->getAbsolutePath() || !file_exists($this->getAbsolutePath())) {
            return null;
        }

        try {
            $file = new File($this->getAbsolutePath());
        } catch (FileNotFoundException $e) {
            $file = null;
        }

        return $file;
    }

    /**
     * Load content of the file from it's path.
     *
     * @return string
     */
    public function getFileContents()
    {
        return file_get_contents($this->getAbsolutePath());
    }

    /**
     * @return mixed
     */
    public function getDescription ()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription ($description)
    {
        $this->description = $description;
    }

    /**
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $request       = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $assetFormData = $request->request->get('asset');

        // only for first time
        if (!isset($assetFormData['unlockId']) || !$assetFormData['unlockId']) {
            $metadata->addPropertyConstraint('file', new NotBlank(array(
                'message' => 'mautic.asset.file.notblank'
            )));
        }
    }
}
