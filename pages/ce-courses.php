<?php
declare(strict_types=1);

global $wpdb;
$xdConnect = new XDConnectAPI();

// Get program name from subtitle or fall back to title
$programName = get_post_meta(get_the_ID(), '_xd_ce_subtitle', true) ?: get_the_title();
$coursesData = $xdConnect->getCoursesByProgramName($programName);
$response = $coursesData['items'] ?? null;
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>

<div id="accordion">
<?php if (empty($response)){ ?>
    <div class="no-courses-msg">
        This program is not offering course enrollment at this time. Please check back soon. 
        For more information call (718) 482-7244.
    </div>
<?php } else {
    $groupedArray = [];
    foreach ($response as $valueArray) {
        $groupKey = substr($valueArray['courseCode'], 0, 7);
        if (!isset($groupedArray[$groupKey])) {
            $groupedArray[$groupKey] = [
                'title' => $valueArray['title'],
                'description' => $valueArray['description'],
                'items' => [],
            ];
        }
        if (
            (date('Y-m-d', strtotime($valueArray['startDate'])) >= date('Y-m-d')) ||
            (
                (in_array($groupKey, ['HSEE099', 'HSES099'])) &&
                (date('Y-m-d', strtotime($valueArray['endDate'])) >= date('Y-m-d'))
            )
        ) {
            $groupedArray[$groupKey]['items'][] = $valueArray;
        }
        if ($valueArray['fee'] == 0) {
            continue;
        }
    }

    foreach ($groupedArray as $groupKey => $group) {
        echo "<br>";
        echo "<h4 class='course-title'>" . htmlspecialchars_decode($group['title']) . "</h4>";
        echo "<div class='course-descr'>" . htmlspecialchars_decode($group['description']) . "</div><br>";
        foreach ($group['items'] as $item) {
            $array_of_time = [
                'startTime' => 'time',
                'endTime' => 'time',
                'startDate' => 'date',
                'endDate' => 'date',
            ];
            foreach ($array_of_time as $aof_key => $aof_value) {
                if (!empty($item[$aof_key])) {
                    $item[$aof_key] = $aof_value === 'time'
                        ? date('h:i A', strtotime($item[$aof_key]))
                        : date('F d, Y', strtotime($item[$aof_key]));
                } else {
                    $item[$aof_key] = null;
                }
            }
?>
    <div class="card">
        <div class="card-header">
            <a class="collapsed card-link" data-toggle="collapse" href="#schID_<?php echo strval($item['schID']); ?>">
                [ <?php echo $item['startDate']; ?> &ndash; <?php echo $item['endDate']; ?> ]
            </a>
        </div>
        <div id="schID_<?php echo strval($item['schID']); ?>" class="collapse" data-parent="#accordion">
            <div class="card-body">
                <p><i><b>Location:</b></i> <?php echo $item['room']; ?></p>
                <p><i><b>Weekday:</b></i> <?php echo $item['daysOfWeek']; ?></p>
                <p><i><b>Time:</b></i> <?php echo $item['startTime']; ?> &ndash; <?php echo $item['endTime']; ?></p>
                <p><i><b>Fee:</b></i> $<?php echo $item['fee']; ?></p>
                <p><i><b>Course Code:</b></i> <?php echo $item['courseCode']; ?></p>
                <p>
                    <a href="https://ce.cuny.edu/laguardia/courseDisplay.cfm?schID=<?php echo strval($item['schID']); ?>"
                       target="_blank" title="Register Now with CUNY Continuing Education">
                       <i><b>Register Now (External Link)</b></i>
                    </a>
                </p>
            </div>
        </div>
    </div>
<?php
        }
    }
} ?>
</div>
<br>