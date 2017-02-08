<?php

class Profiler
{

    const STAT_TIME = 1;
    const STAT_MEM = 2;

    /**
     * @var integer
     */
    protected $timer;
    
    /**
     * @var array
     */
    protected $stats;
    
    /**
     * @var string Test name.
     */
    protected $test;
    
    public function __construct($test)
    {
        $this->test = $test;
    }

    public static function start($test)
    {
        $profiler = new static($test);
        $profiler->startTimer();
        return $profiler;
    }
    
    public function startTimer()
    {
        $this->timer = microtime(true);
    }
    
    public function reset()
    {
        $this->timer = 0;
        $this->stats = [];
    }
    
    public function stop()
    {
        $this->stats[self::STAT_TIME] = microtime(true) - $this->timer;
        $this->stats[self::STAT_MEM] = memory_get_peak_usage();
    }
    
    public function getStats($stat = null)
    {
        if ($stat === null) {
            return $this->stats;
        }
        switch ($stat) {
            case static::STAT_TIME:
            case static::STAT_MEM:
                if (isset($this->stats[$stat])) {
                    return $this->stats[$stat];
                }
        }
        throw new RuntimeException('Statistics not initialized. Start profiler before get statistics.');
    }
    
    public function __toString()
    {
        $this->stop();
        return sprintf(">>>>> %s\nTime: %.2f sec\n\n",
            $this->test,
            $this->getStats(static::STAT_TIME)
        );
    }
}

function concat_val($carry, $item)
{
    return $carry . $item;
}

function generate_id($max = 10)
{
    static $choices = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $id = '';
    while (strlen($id) < $max) {
        $id .= $choices[mt_rand(0, 61)];
    }
    return $id;
}

$x = range(1, 500000);

$profiler = Profiler::start('Array to value with array_reduce() and anon function');
$result = array_reduce($x, function ($carry, $item) {
    return $carry . $item;
}, '');
echo $profiler;

$profiler = Profiler::start('Array to value with array_reduce() and global function');
$result = array_reduce($x, 'concat_val', '');
echo $profiler;

$profiler = Profiler::start('Array to value with foreach');
$carry = '';
foreach ($x as $item) {
    $carry .= $item;
}
echo $profiler;

$profiler = Profiler::start('Array map with array_map() and anon function');
$result = array_map(function ($item) {
    return $item * 2;
}, $x);
echo $profiler;

$profiler = Profiler::start('Array map with foreach');
$result = [];
foreach ($x as $item) {
    $result[] = $item * 2;
}
echo $profiler;

$profiler = Profiler::start('Array map with foreach reference');
$result = $x;
foreach ($result as &$item) {
    $item *= 2;
}
echo $profiler;

$profiler = Profiler::start('Id generator');
for ($i = 0; $i < 50000; $i++) {
    $id = generate_id();
}
echo $profiler;


