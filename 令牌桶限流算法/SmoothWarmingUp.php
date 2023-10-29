<?php


class SmoothWarmingUp {
    private $timestamp;
    private $capacity;  // 桶的容量
    private $rate;      // 桶流出的速率
    private $token;     // 当前积累的请求数(当前积累颁发的token数)

    public function __construct() {
        $this->timestamp = time();
        $this->capacity = 30;
        $this->rate = 5;
    }

    public function grant() {
        $now = time();

        // 上一次请求与当前请求可以流出的token数，可能会超过capacity,但没事，因为每一次请求都会更新timestamp
        $outflowToken = ($now - $this->timestamp) * $this->rate;
        $this->token = max(0, $this->token - $outflowToken); //可能为负数，所以要取最小0，
        $this->timestamp = $now; // 将timestamp更新成上一次请求时间，这样下次进来的时候，就会重新计算outflowtoken

        if (($this->token + 1) <= $this->capacity) {
            // 尝试加入token，并且容器还未满
            $this->token ++;
            return true;
        } else {
            return false;
        }
    }
}

$bucket = new SmoothWarmingUp();
for ($i = 0; $i < 50; $i++) {
    echo "{$i}\t " . var_export($bucket->grant(), true) . PHP_EOL;

}
for ($i = 0; $i < 50; $i++) {
    echo "{$i}\t " . var_export($bucket->grant(), true);
    sleep(1);
}
