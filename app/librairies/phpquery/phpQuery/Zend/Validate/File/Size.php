<?php
/**
 * Zend Framework.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category  Zend
 *
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 *
 * @version   $Id: $
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * Validator for the maximum size of a file up to a max of 2GB.
 *
 * @category  Zend
 *
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_File_Size extends Zend_Validate_Abstract
{
    /**#@+
     * @const string Error constants
     */
    const TOO_BIG = 'fileSizeTooBig';
    const TOO_SMALL = 'fileSizeTooSmall';
    const NOT_FOUND = 'fileSizeNotFound';
    /**#@-*/

    /**
     * @var array Error message templates
     */
    protected $_messageTemplates = [
        self::TOO_BIG   => "The file '%value%' is bigger than allowed",
        self::TOO_SMALL => "The file '%value%' is smaller than allowed",
        self::NOT_FOUND => "The file '%value%' could not be found",
    ];

    /**
     * @var array Error message template variables
     */
    protected $_messageVariables = [
        'min' => '_min',
        'max' => '_max',
    ];

    /**
     * Minimum filesize.
     *
     * @var int
     */
    protected $_min;

    /**
     * Maximum filesize.
     *
     * If null, there is no maximum filesize
     *
     * @var int|null
     */
    protected $_max;

    /**
     * Sets validator options.
     *
     * Min limits the filesize, when used with max=null it is the maximum filesize
     * It also accepts an array with the keys 'min' and 'max'
     *
     * @param int|array $min Minimum filesize
     * @param int       $max Maximum filesize
     *
     * @return void
     */
    public function __construct($min, $max = null)
    {
        if (is_array($min)) {
            $count = count($min);
            if (array_key_exists('min', $min)) {
                if (array_key_exists('max', $min)) {
                    $max = $min['max'];
                }

                $min = $min['min'];
            } elseif ($count === 2) {
                $minValue = array_shift($min);
                $max = array_shift($min);
                $min = $minValue;
            } elseif ($count === 1) {
                $min = array_shift($min);
                $max = null;
            } else {
                $min = 0;
                $max = null;
            }
        }

        if (empty($max)) {
            $max = $min;
            $min = 0;
        }

        $this->setMin($min);
        $this->setMax($max);
    }

    /**
     * Returns the minimum filesize.
     *
     * @param bool $unit Return the value with unit, when false the plan bytes will be returned
     *
     * @return int
     */
    public function getMin($unit = true)
    {
        $unit = (bool) $unit;
        $min = $this->_min;
        if ($unit) {
            $min = $this->_toByteString($min);
        }

        return $min;
    }

    /**
     * Sets the minimum filesize.
     *
     * @param int $min The minimum filesize
     *
     * @throws Zend_Validate_Exception When min is greater than max
     *
     * @return Zend_Validate_File_Size Provides a fluent interface
     */
    public function setMin($min)
    {
        $min = (int) $this->_fromByteString($min);
        if (($this->_max !== null) && ($min > $this->_max)) {
            require_once 'Zend/Validate/Exception.php';

            throw new Zend_Validate_Exception("The minimum must be less than or equal to the maximum filesize, but $min >"
                                            ." {$this->_max}");
        }

        $this->_min = max(0, $min);

        return $this;
    }

    /**
     * Returns the maximum filesize.
     *
     * @param bool $unit Return the value with unit, when false the plan bytes will be returned
     *
     * @return int|null
     */
    public function getMax($unit = true)
    {
        $unit = (bool) $unit;
        $max = $this->_max;
        if ($unit) {
            $max = $this->_toByteString($max);
        }

        return $max;
    }

    /**
     * Sets the maximum filesize.
     *
     * @param int|null $max The maximum filesize
     *
     * @throws Zend_Validate_Exception When max is smaller than min
     *
     * @return Zend_Validate_StringLength Provides a fluent interface
     */
    public function setMax($max)
    {
        $max = (int) $this->_fromByteString($max);
        if (($this->_min !== null) && ($max < $this->_min)) {
            require_once 'Zend/Validate/Exception.php';

            throw new Zend_Validate_Exception('The maximum must be greater than or equal to the minimum filesize, but '
                                            ."$max < {$this->_min}");
        } else {
            $this->_max = $max;
        }

        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface.
     *
     * Returns true if and only if the filesize of $value is at least min and
     * not bigger than max (when max is not null).
     *
     * @param string $value Real file to check for size
     * @param array  $file  File data from Zend_File_Transfer
     *
     * @return bool
     */
    public function isValid($value, $file = null)
    {
        // Is file readable ?
        if (!@is_readable($value)) {
            $this->_throw($file, self::NOT_FOUND);

            return false;
        }

        // limited to 4GB files
        $size = sprintf('%u', @filesize($value));
        $this->_setValue($size);

        // Check to see if it's smaller than min size
        if (($this->_min !== null) && ($size < $this->_min)) {
            $this->_throw($file, self::TOO_SMALL);
        }

        // Check to see if it's larger than max size
        if (($this->_max !== null) && ($this->_max < $size)) {
            $this->_throw($file, self::TOO_BIG);
        }

        if (count($this->_messages) > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Returns the formatted size.
     *
     * @param int $size
     *
     * @return string
     */
    protected function _toByteString($size)
    {
        $sizes = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        for ($i = 0; $size > 1024 && $i < 9; $i++) {
            $size /= 1024;
        }

        return round($size, 2).$sizes[$i];
    }

    /**
     * Returns the unformatted size.
     *
     * @param string $size
     *
     * @return int
     */
    protected function _fromByteString($size)
    {
        if (is_numeric($size)) {
            return (int) $size;
        }

        $type = trim(substr($size, -2));
        $value = substr($size, 0, -2);
        switch (strtoupper($type)) {
            case 'YB':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024);
                break;
            case 'ZB':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024);
                break;
            case 'EB':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024 * 1024);
                break;
            case 'PB':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024);
                break;
            case 'TB':
                $value *= (1024 * 1024 * 1024 * 1024);
                break;
            case 'GB':
                $value *= (1024 * 1024 * 1024);
                break;
            case 'MB':
                $value *= (1024 * 1024);
                break;
            case 'KB':
                $value *= 1024;
                break;
            default:
                break;
        }

        return $value;
    }

    /**
     * Throws an error of the given type.
     *
     * @param string $file
     * @param string $errorType
     *
     * @return false
     */
    protected function _throw($file, $errorType)
    {
        if ($file !== null) {
            $this->_value = $file['name'];
        }

        $this->_error($errorType);

        return false;
    }
}
