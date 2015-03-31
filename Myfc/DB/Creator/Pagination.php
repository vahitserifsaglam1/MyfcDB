<?php
namespace Myfc\DB\Creator;

/**
 *
 * @author vahit�erif
 *        
 */
class Pagination
{

     private $configs;
     
     private $_string;
     
     private $numeric;
     
     private $records;
     
     
     const PAGINATION_CONFIGS = 'app/Configs/paginationConfigs.php';
    /**
     *  
     *  Ba�lat�c� S�n�f
     *  
     *   Dosyay� par�alar
     * 
     */
    public function __construct($parse)
    {
        
        
        if(file_exists(static::PAGINATION_CONFIGS))
        {
            
            $this->configs = require static::PAGINATION_CONFIGS;
            
        }else{
            
            die('Pagination ayarlar� �ekilemedi, dosyan�z silinm�');
            
        }
        
        if(is_array($parse))
        {
            
            $this->parseArray($parse);
            
        }
        if(is_string($parse))
        {
            
            $this->parseString($parse);
            
        }
        
        
    }
    
    /**
     * Stati olarak s�n�f� ba�latmaya yarar
     * @param unknown $parse
     * @return \Myfc\DB\Creator\Pagination
     */
    
    public static function boot($parse)
    {
        
        return new static($parse);
        
    }
    
    /**
     * ayarlar� de�i�tirir 
     * @param array $configs
     * @return \Myfc\DB\Creator\Pagination
     */

    public function setConfigs( array $configs )
    {
        
        $this->configs = $configs;
        
        return $this;
        
    }
    
    /**
     * Gelen veriyi par�alamakta kullan�l�r, veri return etmez(void)
     * @param array $parse
     */
    
    private function parseArray( array $parse )
    {
        
        
        if(is_string($parse[0]) && is_string($parse[1]) || is_numeric($parse[1]))
        {
      
            if(strstr($parse[0], "{") && strstr($parse[0], "}"))
            {
               
                $string = preg_match("#\{(.*?)\}#", $parse[0],$find);
                
                
                
                if($find)
                {
                    
                    
                    $clean = preg_replace("#\{(.*?)\}#", '', $parse[0]);

                    
                    
                    $numeric = $find[array_search($parse[1], $find)];
                    
                  
                    $this->_string= $clean;
                    
                    $this->numeric = $numeric;
                    
                   
                  
                }
                
               
                
            }
            
        }
        
        
        if(is_array($parse[0]) && is_string($parse[1]) || is_numeric($parse[1]))
        {
            
            $array = $parse[0];
            
            $numeric = $array[$parse[1]];
            
            $this->numeric = $numeric;
            
        }
            
        

       
    }
    
    /**
     * Gelen veri stringse kullan�l�r, veri return etmez(void)
     * @param string $string
     */
    private function parseString( $string = '' )
    {
        
        if(is_string($string))
        {
        
          $this->_string = $string;
          
          $this->numeric = 0;
        
        }
        
    }
    
    /**
     * Class,id,data vs gibi html kodlar� olu�turmak i�in kullan�l�r
     * @param array $array
     * @return string
     */
    
    private function creator(array $array)
    {
        
        $msg = '';
        
        foreach($array as $key => $value)
        {
            
            $msg .= "$key='$value' ";
            
        }
        
        return $msg;
        
    }
    
    /**
     * Ebeveyn div in s�n�flar�n� vs �zellikleri i�in kullan�l�r
     * @return string
     */
    private function parentCreator()
    {
        
        $parents = $this->configs['parent'];
        
        $msg = $this->creator($parents);
        
        return $msg;
        
    }
    
    /**
     * �ocuk div lerin s�n�flar�n� vs �zellikleri i�in kullan�l�r
     * @return string
     */
    
    private function childrenCreator()
    {
        
        $childrens = $this->configs['children'];
        
        $msg = $this->creator($childrens);
        
        return $msg;
        
    }
    
    public function setRecords( $records = 0)
    {
        
        $this->records = $records;
        
        return $this;
        
    }
     
    public function paginate( $return= false)
    {

         $parent = $this->parentCreator();
        
         $msg = "<div $parent >";
         $records = $this->records;
         $configs = $this->configs;
         $count = $this->configs['count'];
         
         if(!isset($count) || !is_integer($count))
         {
             
             $count = 50;
             
         }
         
         if(!$records)
         {
             
             $records = 1;
             
         }
         
         if(isset($configs['max']) && isset($configs['max']))
         {
             
             $max = $configs['max'];
             
             $min = $configs['min'];
             
         }else{
             
             $max = 100;
             
             $min = 15;
             
         }
         
         $link = $this->_string;
         
         $numeric = $this->numeric;
         
         if(!is_integer($numeric))
         {
             
             $numeric = 0;
             
         }
         
 
         $ceil = ceil($records / $count);
         
         
         $minpage = ($numeric - $min);
         $minpage = ($minpage < 1) ? 1 : $minpage;
         $maxpage = ($minpage + $max);
         
         $maxpage = ($maxpage > $ceil) ? $ceil : $maxpage;
         
         
         
         for($i = $minpage;$i<=$maxpage;$i++)
         {
         
           
         
             $msg .=  $this->linkCreator($link,$i);
         
         }
          
         
         $msg .= "</div>";
        
         if($return !== false)
         {
             
             return $msg;
             
         }else{
             
             echo $msg;
             
         }
   
        
    }
    
    private function linkCreator($link, $num)
    {
        
        $son = substr($link, strlen($link)-1,strlen($link));
        
        $child = $this->childrenCreator();
        
        if($son == "/")
        {
            
            $msg = "<a href='$link".$num."' $child />$num</a>";
            
        }else{
            
            $msg = "<a href='$link/$num' $child />$num</a>";
            
        }
        
        return $msg;
        
    }
    
    /**
     * Aktif sayfay� d��nd�r�r
     * @return Ambigous <number, unknown, array>
     */
    
    public function getActivePage()
    {
        
        return  $this->numeric;
        
    }
    
    /**
     * A�tif sayfa atamas� yapar
     * @param number $page
     * @return \Myfc\DB\Creator\Pagination
     */
    
    public function setActivePage( $page = 0)
    {
        
        $this->numeric = $page;
        
        return $this;
        
    }
    
    /**
     * Sayfalama yap�l�rkenki maxiumum sayfa say�s�n� belirler
     * @param number $max
     * @return \Myfc\DB\Creator\Pagination
     */
    public function setMax($max = 0)
    {
        
        if(isset($this->configs['max']))
        {
            
            $this->configs['max'] = $max;
            
        }
        return $this;
        
    
    }
    
    /**
     * Sayfalama yaparken ba�lanacak say�y� belirler
     * @param number $min
     * @return \Myfc\DB\Creator\Pagination
     */
    public function setMin($min = 0){
        
        if(isset($this->configs['min'])) 
        {
            
            $this->configs['min'] = $min;
            
        }
        
        return $this;
        
    }
    
 
     /**
      * Max ve min atamas� yapar
      * @param string $max
      * @param string $min
      * @return \Myfc\DB\Creator\Pagination
      */
 
    
    public function setMaxAndMin($max = null, $min = null)
    {
        
        if($max !== null) $this->setMax($max);
        
        if($min !== null) $this->setMin($min);
        
        return $this;
        
    }
    
    /**
     * 
     * @param number $count
     * @return \Myfc\DB\Creator\Pagination
     */
    public function setCount($count = 0)
    {
        
        if(isset($this->configs['count']))
        {
            
            $this->configs['count'] = $count;
            
        }
        
        return $this;
        
    }
    
    /**
     * 
     * @return number
     */
    public function getCount()
    {
        
        if(isset($this->configs['count'])) return $this->configs['count'];else return 50;
        
    }
    
    public function getStartPage()
    {
        
        $numeric = $this->numeric;
        
        $cikart = ($numeric-1);
        
        if($cikart < 1) $cikart = 1;
        
        return $cikart;
        
    }
    
    
}

?>