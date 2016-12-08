<?php
if (!class_exists('ConnectionManager') || Configure::read('debug') < 2) {
    return false;
}
$noLogs = !isset($sqlLogs);
if ($noLogs) {
    $sources = ConnectionManager::sourceList();

    $sqlLogs = array();
    foreach ($sources as $source) {
        $db = ConnectionManager::getDataSource($source);
        if (!method_exists($db, 'getLog')) continue;
        $sqlLogs[$source] = $db->getLog();
    }
}

if ($noLogs || isset($_forced_from_dbo_)):
    foreach ($sqlLogs as $source => $logInfo):
        $text = $logInfo['count'] > 1 ? 'queries' : 'query';
        echo '<table class="table table-striped cake-sql-log" id="cakeSqlLog_' . uniqid() . '" summary="Cake SQL Log" style="font-size:85%" cellspacing="0">';
    ?>
    <thead>
        <tr><th class="col-sm-9">Query</th><th class="col-sm-2">Info</th></tr>
    </thead>
    <tbody>
    <?php
        foreach ($logInfo['log'] as $k => $i) :
            $i += array('error' => '');
            if (!empty($i['params']) && is_array($i['params'])) {
                $bindParam = $bindType = null;
                if (preg_match('/.+ :.+/', $i['query'])) {
                    $bindType = true;
                }
                foreach ($i['params'] as $bindKey => $bindVal) {
                    if ($bindType === true) {
                        $bindParam .= h($bindKey) . " => " . h($bindVal) . ", ";
                    } else {
                        $bindParam .= h($bindVal) . ", ";
                    }
                }
                $i['query'] .= " , params[ " . rtrim($bindParam, ', ') . " ]";
            }
            printf('<tr><td class="col-sm-10"><samp>%s</samp></td><td class="col-sm-2"><span class="label label-danger">Error: %s</span><span class="push-15-l label label-warning">Affected: %s</span><br /><br /><span class="label label-info">Rows: %s</span><span class="push-15-l label label-success">Time: %s</span><br /></td></tr>%s', h($i['query']), $i['error'], $i['affected'], $i['numRows'], $i['took'], "\n" );
        endforeach;
    ?>
    </tbody></table>
    <?php
    endforeach;
else:
    printf('<p>%s</p>', __d('cake_dev', 'Encountered unexpected %s. Cannot generate SQL log.', '$sqlLogs'));
endif;