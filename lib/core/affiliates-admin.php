<?php
/**
 * affiliates-admin.php
 * 
 * Copyright (c) 2010, 2011 "kento" Karim Rahimpur www.itthinx.com
 * 
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 * 
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * This header and all notices must be kept intact.
 * 
 * @author Karim Rahimpur
 * @package affiliates
 * @since affiliates 1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Affiliates overview and summarized statistics.
 */
function affiliates_admin() {

	global $wpdb, $affiliates_options;

	if ( !current_user_can( AFFILIATES_ACCESS_AFFILIATES ) ) {
		wp_die( __( 'Access denied.', 'affiliates' ) );
	}

	echo '<h1>' . __( 'Affiliates Overview', 'affiliates' ) . '</h1>';

	$today = date( 'Y-m-d', time() );
	$day_interval  = 7;
	$min_days_back = 14;
	$max_days_back = 1100;

	// filters
	if (
		isset( $_POST['from_date'] ) ||
		isset( $_POST['thru_date'] ) ||
		isset( $_POST['days_back'] ) ||
		isset( $_POST['clear_filters'] )
	) {
		if ( !wp_verify_nonce( $_POST[AFFILIATES_ADMIN_OVERVIEW_NONCE], 'admin' ) ) {
			wp_die( __( 'Access denied.', 'affiliates' ) );
		}
	}
	$from_date = $affiliates_options->get_option( 'overview_from_date', null );
	$thru_date = $affiliates_options->get_option( 'overview_thru_date', null );
	$days_back = $affiliates_options->get_option( 'overview_days_back', $min_days_back );
	if ( isset( $_POST['clear_filters'] ) ) {
		$affiliates_options->delete_option( 'overview_from_date' );
		$affiliates_options->delete_option( 'overview_thru_date' );
		$affiliates_options->delete_option( 'overview_days_back' );
		$from_date = null;
		$thru_date = null;
		$days_back = $min_days_back;
	} else if ( isset( $_POST['submitted'] ) ) {
		if ( empty( $_POST['from_date'] ) && !empty( $_POST['days_back'] ) ) {
			$days_back = abs( intval( $_POST['days_back'] ) );
			if ( $days_back < $min_days_back ) {
				$days_back = $min_days_back;
			} else if ( $days_back > $max_days_back ) {
				$days_back = $max_days_back;
			}
			$affiliates_options->update_option( 'overview_days_back', $days_back );
			unset( $_POST['from_date'] );
		} else {
			$days_back = null;
			$affiliates_options->delete_option( 'overview_days_back' );
		}
		// filter by date(s)
		if ( !empty( $_POST['from_date'] ) ) {
			$from_date = date( 'Y-m-d', strtotime( $_POST['from_date'] ) );
			$affiliates_options->update_option( 'overview_from_date', $from_date );
		} else {
			$from_date = null;
			$affiliates_options->delete_option( 'overview_from_date' );
		}
		if ( !empty( $_POST['thru_date'] ) ) {
			$thru_date = date( 'Y-m-d', strtotime( $_POST['thru_date'] ) );
			$affiliates_options->update_option( 'overview_thru_date', $thru_date );
		} else {
			$thru_date = null;
			$affiliates_options->delete_option( 'overview_thru_date' );
		}
		// coherent dates
		if ( $from_date && $thru_date ) {
			if ( strtotime( $from_date ) > strtotime( $thru_date ) ) {
				$thru_date = null;
				$affiliates_options->delete_option( 'overview_thru_date' );
			}
		}
	}

	if ( !empty( $from_date ) || !empty( $thru_date ) ) {
		if ( !empty( $from_date ) && !empty( $thru_date ) ) {
			$delta = ( strtotime( $thru_date ) - strtotime( $from_date ) ) / ( 3600 * 24 );
		} else {
			if ( empty( $days_back ) ) {
				if ( !empty( $from_date ) ) {
					$delta = ( strtotime( $today ) - strtotime( $from_date ) ) / ( 3600 * 24 );
				} else {
					$delta = $min_days_back;
				}
			} else {
				$delta = $days_back;
			}
		}
		if ( ( $delta > $max_days_back ) || ( $delta < $min_days_back ) ) {
			if ( $delta > $max_days_back ) {
				$delta = $max_days_back;
			}
			if ( $delta < $min_days_back ) {
				$delta = $min_days_back;
			}
			$days_back = $delta;
			$day_interval = intval( $days_back / 2 );
			if ( !empty( $thru_date ) ) {
				$from_date = date( 'Y-m-d', strtotime( $thru_date ) - $days_back * 3600 * 24 );
			} else {
				$from_date = date( 'Y-m-d', strtotime( $today ) - $days_back * 3600 * 24 );
			}
		} else {
			$days_back = $delta;
			$day_interval = intval( $days_back / 2 );
		}
		if ( empty( $from_date ) && empty( $_POST['days_back']) ) {
			$from_date = date( 'Y-m-d', strtotime( $thru_date ) - $days_back * 3600 * 24 );
		}
		if ( empty( $thru_date ) ) {
			$thru_date = date( 'Y-m-d', strtotime( $from_date ) + $days_back * 3600 * 24 );
		}
	}

	// fill this in before the final $from_date and $thru_date are set
	$filters_form =
		'<div class="filters">' .
			'<label class="description" for="setfilters">' . __( 'Filters', 'affiliates' ) . '</label>' .
			'<form id="setfilters" action="" method="post">' .
				'<p>' .
				'<label class="from-date-filter" for="from_date">' . __( 'From', 'affiliates' ) . '</label>' .
				'<input class="datefield from-date-filter" name="from_date" type="text" value="' . esc_attr( $from_date ) . '"/>'.
				'<label class="thru-date-filter" for="thru_date">' . __( 'Until', 'affiliates' ) . '</label>' .
				'<input class="datefield thru-date-filter" name="thru_date" type="text" value="' . esc_attr( $thru_date ) . '"/>'.
				'<label class="days-back-filter" for="days_back">' . __( 'Days back', 'affiliates' ) . '</label>' .
				'<input class="days-back-filter" name="days_back" type="text" value="' . esc_attr( $days_back ) . '"/>'.
				wp_nonce_field( 'admin', AFFILIATES_ADMIN_OVERVIEW_NONCE, true, false ) .
				'<input class="button" type="submit" value="' . __( 'Apply', 'affiliates' ) . '"/>' .
				'<input class="button" type="submit" name="clear_filters" value="' . __( 'Clear', 'affiliates' ) . '"/>' .
				'<input type="hidden" value="submitted" name="submitted"/>' .
				'</p>' .
			'</form>' .
		'</div>';

	if ( empty( $thru_date ) ) {
		$thru_date = $today;
	}
	if ( empty( $from_date ) ) {
		$from_date = date( 'Y-m-d', strtotime( $thru_date ) - $days_back * 3600 * 24 );
	}

	$affiliates_table = _affiliates_get_tablename( 'affiliates' );
	$hits_table = _affiliates_get_tablename( 'hits' );
	$referrals_table = _affiliates_get_tablename( 'referrals' );

	$affiliates_subquery = " affiliate_id IN (SELECT affiliate_id FROM $affiliates_table WHERE status = 'active') ";

	// hits per day
	$query = "SELECT date, sum(count) as hits FROM $hits_table WHERE date >= %s AND date <= %s AND " . $affiliates_subquery . " GROUP BY date";
	$hit_results = $wpdb->get_results( $wpdb->prepare( $query,
		$from_date, $thru_date, $from_date, $thru_date
	 ));
	$hits = array();
	foreach( $hit_results as $hit_result ) {
		$hits[$hit_result->date] = $hit_result->hits;
	}
	
	// visits per day
	$query = "SELECT count(DISTINCT IP) visits, date FROM $hits_table WHERE date >= %s AND date <= %s AND " . $affiliates_subquery . " GROUP BY date";
	$visit_results = $wpdb->get_results( $wpdb->prepare( $query,
		$from_date, $thru_date, $from_date, $thru_date
	));
	$visits = array();
	foreach( $visit_results as $visit_result ) {
		$visits[$visit_result->date] = $visit_result->visits;
	}
	
	// referrals per day
	$query = "SELECT count(referral_id) referrals, date(datetime) date FROM $referrals_table WHERE status = %s AND date(datetime) >= %s AND date(datetime) <= %s AND " . $affiliates_subquery . " GROUP BY date";
	$results = $wpdb->get_results( $wpdb->prepare( $query,
		AFFILIATES_REFERRAL_STATUS_ACCEPTED, $from_date, $thru_date, $from_date, $thru_date
	));
	$accepted = array();
	foreach( $results as $result ) {
		$accepted[$result->date] = $result->referrals;
	}
	
	$results = $wpdb->get_results( $wpdb->prepare( $query,
		AFFILIATES_REFERRAL_STATUS_CLOSED, $from_date, $thru_date, $from_date, $thru_date
	));
	$closed = array();
	foreach( $results as $result ) {
		$closed[$result->date] = $result->referrals;
	}
	
	$results = $wpdb->get_results( $wpdb->prepare( $query,
		AFFILIATES_REFERRAL_STATUS_PENDING, $from_date, $thru_date, $from_date, $thru_date
	));
	$pending = array();
	foreach( $results as $result ) {
		$pending[$result->date] = $result->referrals;
	}
	
	$results = $wpdb->get_results( $wpdb->prepare( $query,
		AFFILIATES_REFERRAL_STATUS_REJECTED, $from_date, $thru_date, $from_date, $thru_date
	));
	$rejected = array();
	foreach( $results as $result ) {
		$rejected[$result->date] = $result->referrals;
	}

	$accepted_series = array();
	$pending_series  = array();
	$rejected_series = array();
	$closed_series   = array();
	$hits_series     = array();
	$visits_series   = array();
	$ticks           = array();
	$dates           = array();
	for ( $day = -$days_back; $day <= 0; $day++ ) {
		$date = date( 'Y-m-d', strtotime( $thru_date ) + $day * 3600 * 24 );
		$dates[$day] = $date;
		if ( isset( $accepted[$date] ) ) {
			$accepted_series[] = array( $day, intval( $accepted[$date] ) );
		}
		if ( isset( $pending[$date] ) ) {
			$pending_series[]  = array( $day, intval( $pending[$date] ) );
		}
		if ( isset( $rejected[$date] ) ) {
			$rejected_series[] = array( $day, intval( $rejected[$date] ) );
		}
		if ( isset( $closed[$date] ) ) {
			$closed_series[]   = array( $day, intval( $closed[$date] ) );
		}
		if ( isset( $hits[$date] ) ) {
			$hits_series[]   = array( $day, intval( $hits[$date] ) );
		}
		if ( isset( $visits[$date] ) ) {
			$visits_series[]   = array( $day, intval( $visits[$date] ) );
		}
		if ( $days_back <= ( $day_interval + $min_days_back ) ) {
			$label   = date( 'm-d', strtotime( $date ) );
			$ticks[] = array( $day, $label );
		} else if ( $days_back <= 91 ) {
			$d = date( 'd', strtotime( $date ) );
			if (  $d == '1' || $d == '15' ) {
				$label   = date( 'm-d', strtotime( $date ) );
				$ticks[] = array( $day, $label );
			}
		} else {
			if ( date( 'd', strtotime( $date ) ) == '1' ) {
				if ( date( 'm', strtotime( $date ) ) == '1' ) {
					$label   = '<strong>' . date( 'Y', strtotime( $date ) ) . '</strong>';
				} else {
					$label   = date( 'm-d', strtotime( $date ) );
				}
				$ticks[] = array( $day, $label );
			}
		}
	}
	$accepted_series_json = json_encode( $accepted_series );
	$pending_series_json  = json_encode( $pending_series );
	$rejected_series_json = json_encode( $rejected_series );
	$closed_series_json   = json_encode( $closed_series );
	$hits_series_json     = json_encode( $hits_series );
	$visits_series_json   = json_encode( $visits_series );
	$span_series_json     = json_encode( array( array( intval( -$days_back ), 0 ), array( 0, 0 ) ) );
	$ticks_json           = json_encode( $ticks );
	$dates_json           = json_encode( $dates );

	echo '<h2>' . sprintf( __( '%d Day Charts', 'affiliates' ), $days_back ) . '</h2>';
	echo '<div class="manage" style="margin-right:1em">';
	?>
	<div id="stats" class="" style="width:100%;height:400px;"></div>
	<script type="text/javascript">
		(function($){
			$(document).ready(function(){
				var data = [
					{
						label : "<?php _e( 'Hits', 'affiliates' ); ?>",
						data : <?php echo $hits_series_json; ?>,
						lines : { show : true },
						yaxis : 2,
						color : '#ccddff'
					},
					{
						label : "<?php _e( 'Visits', 'affiliates' ); ?>",
						data : <?php echo $visits_series_json; ?>,
						lines : { show : true },
						yaxis : 2,
						color : '#ffddcc'
					},
					{
						label : "<?php _e( 'Accepted', 'affiliates' ); ?>",
						data : <?php echo $accepted_series_json; ?>,
						color : '#009900',
						bars : { align : "center", show : true, barWidth : 1 },
						hoverable : true,
						yaxis : 1
					},
					{
						label : "<?php _e( 'Pending', 'affiliates' ); ?>",
						data : <?php echo $pending_series_json; ?>,
						color : '#0000ff',
						bars : { align : "center", show : true, barWidth : 0.6 },
						yaxis : 1
					},
					{
						label : "<?php _e( 'Rejected', 'affiliates' ); ?>",
						data : <?php echo $rejected_series_json; ?>,
						color : '#ff0000',
						bars : { align : "center", show : true, barWidth : .3 },
						yaxis : 1
					},
					{
						label : "<?php _e( 'Closed', 'affiliates' ); ?>",
						data : <?php echo $closed_series_json; ?>,
						color : '#333333',
						points : { show : true },
						yaxis : 1
					},
					{
						data : <?php echo $span_series_json; ?>,
						lines : { show : false },
						yaxis : 1
					}
				];

				var options = {
					xaxis : {
						ticks : <?php echo $ticks_json; ?>
					},
					yaxis : {
						min : 0,
						tickDecimals : 0
					},
					yaxes : [
						{},
						{ position : 'right' }
					],
					grid : {
						hoverable : true
					},
					legend : {
						position : 'nw'
					}
				};

				$.plot($("#stats"),data,options);

				function statsTooltip(x, y, contents) {
					$('<div id="tooltip">' + contents + '</div>').css( {
						position: 'absolute',
						display: 'none',
						top: y + 5,
						left: x + 5,
						border: '1px solid #333',
						'border-radius' : '4px',
						padding: '6px',
						'background-color': '#ccc',
						opacity: 0.90
					}).appendTo("body").fadeIn(200);
				}

				var tooltipItem = null;
				var statsDates = <?php echo $dates_json; ?>;
				$("#stats").bind("plothover", function (event, pos, item) {
					if (item) {
						if (tooltipItem === null || item.dataIndex != tooltipItem.dataIndex || item.seriesIndex != tooltipItem.seriesIndex) {
							tooltipItem = item;
							$("#tooltip").remove();
							var x = item.datapoint[0];
								y = item.datapoint[1];
							statsTooltip(
								item.pageX,
								item.pageY,
								item.series.label + " : " + y +  '<br/>' + statsDates[x] 
							);
						}
					} else {
						$("#tooltip").remove();
						tooltipItem = null;
					}
				});
			});
		})(jQuery);
	</script>
	<?php
	echo '<br class="clear"/>';
	echo $filters_form;
	echo '</div>';
	
	echo '<h2>' . __( 'Statistics Summary', 'affiliates' ) . '</h2>';
	for ( $i = 0; $i < 3; $i++ ) {
		$add_class = "";
		switch ( $i ) {
			case 0:
				$affiliates = affiliates_get_affiliates( true, true );
				$title = __( 'From operative affiliates:', 'affiliates' );
				$info = sprintf( _n( 'There is 1 operative affiliate', 'There are %d operative affiliates', count( $affiliates ), 'affiliates' ), count( $affiliates ) );
				$add_class = "active valid";
				break;
			case 1:
				$affiliates = affiliates_get_affiliates( true, false );
				$title = __( 'From operative and non-operative affiliates:', 'affiliates' );
				$info = sprintf( _n( 'There is 1 affiliate in this set', 'There are %d affiliates in this set', count( $affiliates ), 'affiliates' ), count( $affiliates ) );
				$add_class = "active";
				break;
			case 2:
				$affiliates = affiliates_get_affiliates( false, false );
				$title = __( 'All time (includes data from deleted affiliates):', 'affiliates' );
				$info = sprintf( _n( 'There is 1 affiliate in this set', 'There are %d affiliates in this set', count( $affiliates ), 'affiliates' ), count( $affiliates ) );
				break;
		}
		$hits               = 0;
		$visits             = 0;
		$referrals_accepted = 0;
		$referrals_closed   = 0;
		$referrals_pending  = 0;
		$referrals_rejected = 0;
		foreach ( $affiliates as $affiliate ) {
			$affiliate_id = $affiliate['affiliate_id'];
			$hits      += affiliates_get_affiliate_hits( $affiliate_id );
			$visits    += affiliates_get_affiliate_visits( $affiliate_id );
			$referrals_accepted += affiliates_get_affiliate_referrals( $affiliate_id, null, null, AFFILIATES_REFERRAL_STATUS_ACCEPTED );
			$referrals_closed   += affiliates_get_affiliate_referrals( $affiliate_id, null, null, AFFILIATES_REFERRAL_STATUS_CLOSED );
			$referrals_pending  += affiliates_get_affiliate_referrals( $affiliate_id, null, null, AFFILIATES_REFERRAL_STATUS_PENDING );
			$referrals_rejected += affiliates_get_affiliate_referrals( $affiliate_id, null, null, AFFILIATES_REFERRAL_STATUS_REJECTED );
		}
		
		$accepted_icon = "<img class='icon' alt='" . __( 'Accepted', 'affiliates') . "' src='" . AFFILIATES_PLUGIN_URL . "images/accepted.png'/>";
		$closed_icon = "<img class='icon' alt='" . __( 'Closed', 'affiliates') . "' src='" . AFFILIATES_PLUGIN_URL . "images/closed.png'/>";
		$pending_icon = "<img class='icon' alt='" . __( 'Pending', 'affiliates') . "' src='" . AFFILIATES_PLUGIN_URL . "images/pending.png'/>";
		$rejected_icon = "<img class='icon' alt='" . __( 'Rejected', 'affiliates') . "' src='" . AFFILIATES_PLUGIN_URL . "images/rejected.png'/>";
		
		echo '<div class="manage" style="margin-right:1em">';
		echo '<p>';
		echo '<strong>' . $title . '</strong>&nbsp;' . $info;
		echo '</p>';
		echo '<ul>';
		echo '<li>' . __( '<strong>Referrals:</strong>', 'affiliates' ) . '</li>';
		echo '<li><ul>';
		echo '<li>' . $accepted_icon . '&nbsp;' . sprintf( __( '%10d Accepted', 'affiliates' ), $referrals_accepted ) . '</li>';
		echo '<li>' . $closed_icon . '&nbsp;' . sprintf( __( '%10d Closed', 'affiliates' ), $referrals_closed ) . '</li>';
		echo '<li>' . $pending_icon . '&nbsp;' . sprintf( __( '%10d Pending', 'affiliates' ), $referrals_pending ) . '</li>';
		echo '<li>' . $rejected_icon . '&nbsp;' . sprintf( __( '%10d Rejected', 'affiliates' ), $referrals_rejected ) . '</li>';
		echo '</li></ul>';
		echo '<li>' . sprintf( __( '%10d Hits', 'affiliates' ), $hits ) . '</li>';
		echo '<li>' . sprintf( __( '%10d Visits', 'affiliates' ), $visits ) . '</li>';
		echo '</ul>';
		echo '</div>';
	}
	affiliates_footer();
}
