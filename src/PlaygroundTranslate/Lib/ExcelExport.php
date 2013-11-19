<?php

Namespace Citroen\Lib;

/**
 * ExcelExport object.
 * This object is used to ease creating exports in the Excel format.
 *
 * @see LoaderTool
 * @package    easyCreaDoc
 * @subpackage lib.tools
 * @author     Pierre-Yves Landuré <py.landure@dorigo.fr>
 * @version    SVN: $Id$
 */
class ExcelExport
{

  /**
   *  The string cell value type.
   */
  const STRING = 'string';

  /**
   *  The number cell value type.
   */
  const NUMBER = 'number';

  /**
   *  The DATE cell value type.
   */
  const DATE = 'date';

  /**
   * The culture to use for this excel export.
   * This value determines the format for numbers and dates.
   * 
   * @var string
   * @access protected
   */
  protected $culture;

  /**
   * Array of columns descriptions.
   * Its value should look like :
   *   array(
   *       'column-code' => array (
   *           'title' => 'Column title',
   *           'type' => ExcelExport::NUMBER,
   *         ),
   *       ...
   *     );
   * 
   * @var array
   * @access protected
   */
  protected $columns;

  /**
   * Cache of columns codes.
   * 
   * @var array
   * @access protected
   */
  protected $column_codes;

  /**
   * Array of content lines.
   * 
   * @var array
   * @access protected
   */
  protected $contents;



  /**
   * This object constructor.
   * 
   * @param array $columns The columns descriptions
   * @param array $culture A optionnal culture.
   * @access public
   * @return void
   * @see ExcelExport::setColumns();
   */
  public function __construct($columns = null, $culture = null)
  {
    $this->setColumns($columns);
    $this->contents = array();
    $this->setCulture($culture);
  } // __construct()



  /**
   * Set this excel export columns description.
   * A columns description should look like :
   *
   *   array(
   *       'column-code' => array (
   *           'title' => 'Column title',
   *           'type' => ExcelExport::NUMBER,
   *         ),
   *       'string-column' => 'Column title', // this column default as a ExcelExport::STRING column.
   *     );
   *
   * @param array $columns The columns description.
   * @access public
   * @return void
   * @throw Exception if the columns description is invalid.
   */
  public function setColumns($columns)
  {
    if($columns) // Test if columns is set.
    {
      foreach($columns as $code => $column) // For each column description
      {
        // We clean up the column description.
        $column_description_type = gettype($column);
        switch($column_description_type) // According to the column description type.
        {
          case 'string':
            $column = array(
                        'title' => $column,
                        'type' => self::STRING,
                      );
            $columns[$code] = $column;
            break;
          case 'array':
            if(isset($column['type'])) // Test if type is defined.
            {
              if(! in_array($column['type'], array(self::STRING,
                      self::NUMBER, self::DATE))) // Test if type is known.
              {
                throw new Exception(sprintf('Column "%s" : invalid type "%s".', $code, $column['type']));
              } // Test if type is known.
            }
            else // Test if type is defined.
            {
              $column['type'] = self::STRING;
              $columns[$code] = $column;
            } // Test if type is defined.
            break;
          default:
            throw new Exception(sprintf('Column "%s" : description must be string or array.', $code));
        } // According to the column description type.
      } // For each column description.

      $this->columns = $columns;
      $this->column_codes = null;
    }
    else // Test if columns is set.
    {
      $this->column_codes = null;
      $this->columns = null;
    } // Test if columns is set.

  } // setColumns()



  /**
   * Get this excel export columns description.
   * 
   * @access public
   * @return array A array of columns descriptions.
   * @see ExcelExport::setColumns()
   */
  public function getColumns()
  {
    return $this->columns;
  } // getColumns()



  /**
   * Get this excel export column codes.
   * 
   * @access public
   * @return array A array of column codes.
   */
  public function getColumnCodes()
  {
    if(is_null($this->column_codes)) // Test if column codes already fetched.
    {
      $this->column_codes = array_keys($this->getColumns());
    } // Test if column codes already fetched.

    return $this->column_codes;
  } // getColumnCodes()



  /**
   * Get a column description array according to its code.
   * 
   * @param string $column_code A column code.
   * @access public
   * @return array The column description.
   * @throw Exception if the column does not exists.
   */
  public function getColumn($column_code)
  {
    if(!isset($this->columns[$column_code])) // Test if column is defined.
    {
      throw new Exception(sprintf('Column "%s" is not defined.', $column_code));
      return null;
    } // Test if column is defined.

    return $this->columns[$column_code];
  } // getColumn()



  /**
   * Get a column title according to its code.
   * 
   * @param string $column_code A column code.
   * @access public
   * @return string The column title.
   */
  public function getColumnTitle($column_code)
  {
    $column_description = $this->getColumn($column_code);

    if(isset($column_description['title'])) // Test if title defined.
    {
      return $column_description['title'];
    } // Test if title defined.

    return $column_code;
  } // getColumnTitle()



  /**
   * Get a column type according to its code.
   * 
   * @param string $column_code A column code.
   * @access public
   * @return string A column type.
   */
  public function getColumnType($column_code)
  {
    $column_description = $this->getColumn($column_code);
    return $column_description['type'];
  } // getColumnType()



  /**
   * Set this excel export culture.
   * 
   * @param string $culture 
   * @access public
   * @return void
   */
  public function setCulture($culture)
  {
    $this->culture = $culture;
  } // setCulture()



  /**
   * Get this excel export culture.
   * 
   * @access public
   * @return string A culture.
   */
  public function getCulture()
  {
    if(is_null($this->culture))
    {
      $this->culture = 'en';
    }

    return $this->culture;
  } // getCulture()



  /**
   * Format the given time as string according to this object culture.
   * 
   * @param integer $time A time in the Unix timestamp format.
   * @access public
   * @return string
   */
  public function formatDate($time)
  {
    $value = '';

    if(!is_null($time))
    {
      switch($this->getCulture()) // According to the culture.
      {
        case 'fr':
          $value = date('d/m/Y', $time);
          break;
        case 'en':
        default:
          $value = date('m/d/Y', $time);
      } // According to the culture.
    }

    return $value;
  } // formatDate()



  /**
   * Format the given number as string according to this object culture.
   * 
   * @param float $number A number.
   * @access public
   * @return string
   */
  public function formatNumber($number)
  {
    $value = '';

    if(!is_null($number)) // Test if number is null.
    {
      $value = strval($number);
    } // Test if number is null.

    return $value;
  } // formatNumber()



  /**
   * Format the value according to column type and export culture.
   * 
   * @param string $column_code A column code.
   * @param mixed $value A value.
   * @access public
   * @return string Return the value as a localized string.
   */
  public function formatColumnValue($column_code, $value)
  {
    if(is_null($value)) // Test if value is null.
    {
      return '';
    }

    $column_type = $this->getColumnType($column_code);

    switch($column_type)
    {
      case self::NUMBER:
        $value = nl2br(htmlspecialchars($this->formatNumber($value)));
        break;
      case self::DATE:
        $value = nl2br(htmlspecialchars($this->formatDate($value)));
        break;
      case self::STRING:
      default:
        $value = sprintf('&nbsp;%s', nl2br(htmlspecialchars(strval($value))));
    }

    return $value;
  } // formatColumnValue();



  /**
   * Add a content line to this excel export.
   * 
   * @param array $content_line A content line.
   * @access public
   * @return integer The number of added lines.
   * @throw Exception if the line has a missing column.
   */
  protected function addContentLine($content_line)
  {
    // We test if the content line has all of excel export columns.
    $column_codes = $this->getColumnCodes();
    $intersection = array_intersect_key($column_codes, array_keys($content_line));

    if(count($intersection) != count($column_codes)) // Test if content line as all needed columns.
    {
      throw new Exception('A content line has missing columns.');
      return 0;
    } // Test if content line as all needed columns.

    $this->contents[] = $content_line;

    return 1;
  } // addContentLine()



  /**
   * Add contents to this excel export.
   * 
   * @param array $content A content line, or a array of content lines.
   * @access public
   * @return integer The number of added lines.
   * @throw Exception if a content line has a missing column.
   */
  public function addContent($content)
  {
    if($content) // Test if content.
    {
      // Set the pointer to the first element.
      reset($content);
      if(is_array(current($content))) // Test if content is a array of content lines.
      {
        $count = 0;

        foreach($content as $index => $content_line) // For each content line.
        {
          $count += $this->addContentLine($content_line);
        } // For each content line.

        return $count;
      }
      else // Test if content is a array of content lines.
      {
        return $this->addContentLine($content);
      } // Test if content is a array of content lines.
    } // Test if content.

    return 0;
  } // addContent()



  /**
   * Return this excel export content array.
   * 
   * @access public
   * @return array A array.
   */
  public function getContents()
  {
    return $this->contents;
  } // getContents()


  /**
   * Get this excel exports contents.
   * 
   * @access public
   * @return string The excel contents.
   */
  public function &getExcelContents()
  {
    /**
     * HTML header.
     */
    $excel_contents='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">
<head>
  <meta http-equiv="Content-type" content="text/html;charset=utf-8" />
  <style type="text/css">
  <!--
    table {
      mso-displayed-decimal-separator:"\.";
      mso-displayed-thousand-separator:"\,";
    }

    br {
      mso-data-placement:same-cell;
    }
  -->
  </style>
</head>
<body>
<div id="Classeur1_16681" align="center" x:publishsource="Excel">
<table>';

    $column_codes = $this->getColumnCodes();


    /**
     * Columns' titles.
     */
    $excel_contents .= '<tr>';

    foreach($column_codes as $column_code) // For each column code.
    {
      $excel_contents .= sprintf('<th>&nbsp;%s</th>', nl2br(htmlspecialchars($this->getColumnTitle($column_code))));
    } // For each column code.

    $excel_contents .= '</tr>';


    /**
     * Columns' contents.
     */
    $contents = $this->getContents();

    foreach($contents as $content_line) // For each content line.
    {
      $excel_contents .= '<tr>';
      foreach($column_codes as $column_code) // For each column code.
      {
        $excel_contents .= sprintf('<td>%s</td>', $this->formatColumnValue($column_code, $content_line[$column_code]));
      } // For each column code.
      $excel_contents .= '</tr>';
    } // For each content line.


    /**
     * HTML footer.
     */
    $excel_contents .= '</table>
</div>
</body>
</html>';

    return $excel_contents;
  } // getExcelContents()



  /**
   * Propose this excel export to download.
   * 
   * @param string $filename The excel file name.
   * @param boolean $inline True to display the excel file inline, default to false.
   * @access public
   * @return void
   * @see LoaderTool::downloadContent()
   */
  public function download($filename, $inline = false)
  {
    $excel_contents = $this->getExcelContents();
    return LoaderTool::downloadContent($excel_contents, $filename, $inline, 'application/vnd.msexcel');
    // TODO : $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', "binary");
  } // download()



} // class ExcelExport
