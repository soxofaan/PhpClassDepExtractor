<?php
require_once dirname(__FILE__) . '/PhpClassDepExtractor.php';

class PhpClassHierarchy extends PhpClassDepExtractor
{
    private static $instance;
    public static function instance ()
    {
        if (empty(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c();
        }
        return self::$instance;
    }

    private $classes = array();
    public function run ($argv = array())
    {
        error_reporting(0);
        $this->parseOptions($argv);
        $this->classes = $this->getClasses();
        $result = $this->rc_search(self::$argv);
        $has_jsontool = shell_exec('which json');
        if (empty($has_jsontool))
            var_export($result);
        else
            echo json_encode($result);
    }
    
    private function rc_search($q = array(), $level = 0)
    {
        $classes = array();
        if (empty($level))
        {
            $matches = array();
            $regexp = '/('.implode('|', $q).')/i';
            foreach ($this->classes as $class => $dependency)
            {
                if (preg_match($regexp, $class))
                    $matches[] = $class;
            }
            $classes = $this->rc_search($matches, $level + 1);
        }
        else foreach ($q as $k => $s)
        {
            if (array_key_exists($s, $this->classes))
            {
                if (empty($this->classes[$s]))
                {
                    $classes[] = $s;
                }
                else
                {
                    $classes[$s] = $this->rc_search($this->classes[$s], $level + 1);
                }
            }
            else
            {
                // Class do not exist;
                $classes[$s] = NULL;
            }
        }
        return $classes;
    }
    
    private function parseOptions ($argv)
    {
        self::$argv = $argv;
        unset(self::$argv[0]);
        foreach (self::$argv as $i => $argument)
        {
            if (strpos($argument, '-') === 0)
            {
                unset(self::$argv[$i]);
                switch ($argument)
                {
                	case '--force': case '-force': case '-f':
                        self::$options[] = 'force';
                        break;
                	default:
                	    self::$options[] = preg_replace('/^-*/', '', $argument);
                	    break;
                }
            }
        }
    }

    public static $argv = array();
    public static $options = array();
    
    // git ls-files | grep php$ > var/log/class.files
    public static $filelists = 'var/log/class.files';
    public static $classDeps = 'var/log/class.json';

    private function getClasses ()
    {
        if (is_file(self::$classDeps) && ! in_array('force', self::$options))
            return json_decode(file_get_contents(self::$classDeps), true);
        
        $files = $this->getFileLists();
        $classes = PhpClassDepExtractor::extractFromFiles($files);
        file_put_contents(self::$classDeps, json_encode($classes));
        return $classes;
    }
    
    private function getFileLists()
    {
        if (! is_file(self::$filelists))
        {
            error_log(self::$filelists . ' not found');
            shell_exec("git ls-files | grep php$ > var/log/class.files");
        }
        return explode("\n", file_get_contents(self::$filelists));
    }
}

if (PHP_SAPI == 'cli') PhpClassHierarchy::instance()->run($argv);
