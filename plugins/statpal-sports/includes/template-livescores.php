<?php
if (!defined('ABSPATH')) exit;

/**
 * Template for rendering the Live Scores UI.
 * 
 * Variables available:
 * @var array $matches Extracted match list.
 */
?>
<div class="statpal-livescores-container" data-paginated="true">
    
    <!-- Header -->
    <div class="statpal-ls-header">
        <div class="statpal-ls-header-col first">Time</div>
        <div class="statpal-ls-header-col second">Scores</div>
        <div class="statpal-ls-header-col third">Team</div>
    </div>
    <?php foreach ($matches as $match) : 
        $home_team = $match['home'] ?? [];
        $away_team = $match['away'] ?? [];
        
        $home_name = $home_team['name'] ?? $match['home_team'] ?? 'Home';
        $away_name = $away_team['name'] ?? $match['away_team'] ?? 'Away';
        
        $home_logo = $this->get_team_logo($home_team);
        $away_logo = $this->get_team_logo($away_team);
        
        // Time / Date parsing
        $raw_date = $match['date'] ?? '';
        $raw_time = $match['time'] ?? '';
        
        // Format Date
        $fmt_date = $raw_date;
        if (strtotime($raw_date)) {
            $fmt_date = date('M jS', strtotime($raw_date));
        } elseif(empty($fmt_date) && strtotime($raw_time)) {
             $fmt_date = date('M jS', strtotime($raw_time));
        } else {
             $fmt_date = 'Today';
        }

        // Format Time
        $fmt_time = $raw_time;
        if (strtotime($raw_time)) {
            $fmt_time = date('g:i A', strtotime($raw_time));
        } else {
            $fmt_time = $match['status'] ?? 'Scheduled';
        }
        
        $periods_header = ['1', '2', '3', 'T'];
        $away_scores = ['-', '-', '-', $away_team['totalscore'] ?? '-'];
        $home_scores = ['-', '-', '-', $home_team['totalscore'] ?? '-'];

        if (isset($away_team['innings']['inning']) && is_array($away_team['innings']['inning'])) {

            $away_scores = [];
            $home_scores = [];
            $periods_header = [];

            $awayInnings = $away_team['innings']['inning'] ?? [];
            $homeInnings = $home_team['innings']['inning'] ?? [];

            $maxInnings = max(count($awayInnings), count($homeInnings));

            for ($i = 0; $i < $maxInnings; $i++) {

                $periods_header[] = (string)($i + 1);

                // Away score
                $away_scores[] = isset($awayInnings[$i]['score'])
                    ? $awayInnings[$i]['score']
                    : '-';

                // Home score
                $home_scores[] = isset($homeInnings[$i]['score'])
                    ? $homeInnings[$i]['score']
                    : '-';
            }

            $away_scores[] = $away_team['totalscore'] ?? '-';
            $home_scores[] = $home_team['totalscore'] ?? '-';

            $periods_header[] = 'T';

        }
    ?>
        <!-- statpal-page-item ensures that this row is targeted by the JS pagination logic -->
        <div class="statpal-ls-row statpal-page-item">
            
            <!-- TIME COLUMN -->
            <div class="statpal-ls-row-col first">
                <span class="day"><?php echo esc_html($fmt_date); ?></span>
                <span class="time"><?php echo esc_html($fmt_time); ?></span>
                <span class="status-str"><?php echo esc_html($match['status']); ?></span>
            </div>

            <!-- SCORES COLUMN -->
            <div class="statpal-ls-row-col second">
                <table class="score-table">
                    <tr>
                    <?php foreach($periods_header as $ph) : ?>
                        <th style="font-weight:<?php echo ($ph==='T'?'bold':'normal'); ?>;"><?php echo esc_html($ph); ?></th>
                    <?php endforeach; ?>
                    </tr>
                    
                    <!-- Away Row -->
                    <tr>
                    <?php foreach($away_scores as $i => $sc) : ?>
                        <td style="color:<?php echo ($i==count($away_scores)-1?'#14171a':'#657786'); ?>; font-weight:<?php echo ($i==count($away_scores)-1?'bold':'normal'); ?>;">
                            <?php echo esc_html((string)$sc); ?>
                        </td>
                    <?php endforeach; ?>
                    </tr>
                    
                    <!-- Home Row -->
                    <tr>
                    <?php foreach($home_scores as $i => $sc) : ?>
                        <td style="color:<?php echo ($i==count($home_scores)-1?'#14171a':'#657786'); ?>; font-weight:<?php echo ($i==count($home_scores)-1?'bold':'normal'); ?>;">
                            <?php echo esc_html((string)$sc); ?>
                        </td>
                    <?php endforeach; ?>
                    </tr>
                </table>
            </div>

            <!-- TEAM COLUMN -->
            <div class="statpal-ls-row-col third team-list-col">
                <div class="mobile-heading">Team</div>
                <!-- Away -->
                <div class="team-row">
                    <?php if($away_logo) : ?>
                        <div class="team-logo"><img src="<?php echo esc_url($away_logo); ?>" alt="Logo"></div>
                    <?php else : ?>
                        <div class="team-circle"></div>
                    <?php endif; ?>
                    <div class="team-name"><?php echo esc_html($away_name); ?></div>
                </div>
                
                <!-- Home -->
                <div class="team-row">
                    <?php if($home_logo) : ?>
                        <div class="team-logo"><img src="<?php echo esc_url($home_logo); ?>" alt="Logo"></div>
                    <?php else : ?>
                        <div class="team-circle"></div>
                    <?php endif; ?>
                    <div class="team-name disable"><?php echo esc_html($home_name); ?></div>
                </div>

            </div> <!-- close TEAM COLUMN -->
            
        </div> <!-- close row -->
    <?php endforeach; ?>
    
</div>
 