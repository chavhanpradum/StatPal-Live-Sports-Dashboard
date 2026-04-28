<?php
if (!defined('ABSPATH')) exit;

/**
 * Template for rendering the Recent Matches UI.
 * 
 * Variables available:
 * @var array $items Extracted match list.
 * @var StatPal_Render $this The render class instance
 */
?>
<div class="statpal-livescores-container" data-paginated="true">
     <div class="statpal-ls-header">
        <div class="statpal-ls-header-col first">Time</div>
        <div class="statpal-ls-header-col four">Recent Games</div>
    </div>
    
    <?php 
    $rendered_count = 0;
    
    foreach ($items as $match) : 
        $rendered_count++;

        if (!is_array($match)) continue;

        $home_team = $match['home'] ?? [];
        $away_team = $match['away'] ?? [];
        
        $home_name = is_array($home_team) ? ($home_team['name'] ?? '') : (is_string($home_team) ? $home_team : '');
        $home_name = $home_name ?: ($match['home_team'] ?? 'Home');

        $away_name = is_array($away_team) ? ($away_team['name'] ?? '') : (is_string($away_team) ? $away_team : '');
        $away_name = $away_name ?: ($match['away_team'] ?? 'Away');
        
        $home_logo = $this->get_team_logo($home_team);
        $away_logo = $this->get_team_logo($away_team);
        
        $raw_date = $match['date'] ?? '';
        $raw_time = $match['time'] ?? '';
        
        $fmt_date = $raw_date;
        if (strtotime($raw_date)) $fmt_date = date('M jS', strtotime($raw_date));
        elseif(empty($fmt_date) && strtotime($raw_time)) $fmt_date = date('M jS', strtotime($raw_time));
        else $fmt_date = 'Ended';

        $fmt_time = $raw_time;
        if (strtotime($raw_time)) $fmt_time = date('g:i A', strtotime($raw_time));
        else $fmt_time = $match['status'] ?? 'Final';
    ?>
        <div class="statpal-card statpal-page-item">
            <div class="statpal-ls-row">
                
                
                
                <div class="statpal-ls-row-col first">
                    <span class="day"><?php echo esc_html($fmt_date); ?></span>
                    <span class="time"><?php echo esc_html($fmt_time); ?></span>
                    <span class="status-str"><?php echo esc_html($match['status']); ?></span>
                </div>

                <div class="statpal-ls-row-col four">
                    <div class="team-row-wrap">
                        <div class="team-row">
                            <?php if($away_logo) : ?>
                                <div class="team-logo"><img src="<?php echo esc_attr($away_logo); ?>" alt="Logo"></div>
                            <?php else : ?>
                                <div class="team-circle"></div>
                            <?php endif; ?>
                            <div class="team-name"><?php echo esc_html($away_name); ?></div>
                            <span class="score"><?php echo esc_html($away_team['totalscore']); ?></span>
                        </div>
                        
                        <div class="team-row">
                            <?php if($home_logo) : ?>
                                <div class="team-logo"><img src="<?php echo esc_attr($home_logo); ?>" alt="Logo"></div>
                            <?php else : ?>
                                <div class="team-circle"></div>
                            <?php endif; ?>
                            <div class="team-name disable"><?php echo esc_html($home_name); ?></div>
                            <span class="score"><?php echo esc_html($home_team['totalscore']); ?></span>
                        </div>
                    </div>

                    <button type="button" class="statpal-btn-details statpal-info-btn for-desktop">Game Info</button>
                    
                </div>
                <button type="button" class="statpal-btn-details statpal-info-btn for-mobile">Game Info</button>
            </div>
            
            <div class="statpal-card-details" style="display:none;">
                <div>
                   
                    <strong>Venue:</strong> <?php echo esc_html($match['venue'] ?? $match['stadium'] ?? 'TBD'); ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if($rendered_count === 0) : ?>
        <div class="statpal-empty">No Data Available</div>
    <?php endif; ?>
</div>
