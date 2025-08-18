<?php

namespace app\Utils;

class CronHelper
{
    /**
     * Basit 5 alanlı cron (m h dom mon dow) next run hesaplayıcı
     * Destek: * , */n , a-b , a,b,c , aralık + adım (a-b/n)
     */
    public static function nextRunAt(string $expr, ?\DateTime $from = null, int $maxMinutesScan = 365*24*60): ?string
    {
        $from = $from ?: new \DateTime('now');
        $parts = preg_split('/\s+/', trim($expr));
        if (count($parts) !== 5) { return null; }
        [$minExp, $hourExp, $domExp, $monExp, $dowExp] = $parts;

        $minutes = self::expandField($minExp, 0, 59);
        $hours   = self::expandField($hourExp, 0, 23);
        $months  = self::expandField($monExp, 1, 12);
        $doms    = self::expandField($domExp, 1, 31);
        $dows    = self::expandField($dowExp, 0, 6); // 0=Sun
        if ($minutes===null||$hours===null||$months===null||$doms===null||$dows===null) { return null; }

        $scan = 0;
        $dt = clone $from;
        $dt->modify('+1 minute'); // bir sonraki dakikadan başla
        while ($scan < $maxMinutesScan) {
            $m = (int)$dt->format('n');
            $dom = (int)$dt->format('j');
            $h = (int)$dt->format('G');
            $i = (int)$dt->format('i');
            $dow = (int)$dt->format('w');
            if (in_array($m, $months, true)
                && in_array($dom, $doms, true)
                && in_array($h, $hours, true)
                && in_array($i, $minutes, true)
                && in_array($dow, $dows, true)) {
                return $dt->format('Y-m-d H:i:00');
            }
            $dt->modify('+1 minute');
            $scan++;
        }
        return null;
    }

    private static function expandField(string $exp, int $min, int $max): ?array
    {
        $result = [];
        foreach (explode(',', $exp) as $token) {
            $token = trim($token);
            if ($token === '*') {
                $result = array_merge($result, range($min, $max));
                continue;
            }
            $step = 1;
            if (strpos($token, '/') !== false) {
                [$base, $s] = explode('/', $token, 2);
                $token = $base; $step = max(1, (int)$s);
            }
            if ($token === '*') {
                $range = range($min, $max, $step);
                $result = array_merge($result, $range);
                continue;
            }
            if (strpos($token, '-') !== false) {
                [$a, $b] = explode('-', $token, 2);
                $a = max($min, (int)$a); $b = min($max, (int)$b);
                if ($a > $b) { return null; }
                for ($v = $a; $v <= $b; $v += $step) { $result[] = $v; }
            } else {
                $v = (int)$token;
                if ($v < $min || $v > $max) { return null; }
                if ((($v - $min) % $step) === 0) { $result[] = $v; } else { $result[] = $v; }
            }
        }
        $result = array_values(array_unique($result)); sort($result);
        return $result;
    }
}


