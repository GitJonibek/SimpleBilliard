<?php
App::uses('AppHelper', 'View/Helper');

/**
 * Class NumberExHelper
 */
class NumberExHelper extends AppHelper
{
    /**
     * 数字を単位付きフォーマットに変換する
     * e.g.
     *   999         -> 999
     *   1200        -> 1.2K
     *   12000       -> 12K
     *   1200000     -> 1.2M
     *   12000000    -> 12M
     *   1200000000  -> 1.2G
     *   12000000000 -> 12G
     *
     * @param int   $num  フォーマットする数値
     * @param array $options
     *                    convert_start: int  $num がこの値より大きな数値の場合だけフォーマットする
     *
     * @return string
     */
    public function formatHumanReadable($num, $options = [])
    {
        $options = array_merge(
            [
                'convert_start' => 0,
            ], $options);

        // convert_start よりも小さい場合は何もしない
        if ($num < $options['convert_start']) {
            return $num;
        }

        if ($num < 1000) {
            // pass
        } elseif ($num < 10000) {
            // "1.2K"
            $num = sprintf("%.1fK", round($num / 1000, 1, PHP_ROUND_HALF_DOWN));
        } elseif ($num < 1000000) {
            // "12K"
            $num = sprintf("%dK", floor($num / 1000));
        } elseif ($num < 10000000) {
            // "1.2M"
            $num = sprintf("%.1fM", round($num / 1000000, 1, PHP_ROUND_HALF_DOWN));
        } elseif ($num < 1000000000) {
            // "12M"
            $num = sprintf("%dM", floor($num / 1000000));
        } elseif ($num < 10000000000) {
            // "1.2G"
            $num = sprintf("%.1fG", round($num / 1000000000, 1, PHP_ROUND_HALF_DOWN));
        } elseif ($num < 1000000000000) {
            // "12G"
            $num = sprintf("%dG", floor($num / 1000000000));
        }
        return $num;
    }

}
