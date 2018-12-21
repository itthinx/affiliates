/*!
 * dashboard-overview-graph.js
 *
 * Copyright (c) 2010 - 2018 "kento" Karim Rahimpur www.itthinx.com
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
 * @since affiliates 4.0.0
 */

var affiliates_dashboard_overview_graph = {};

( function( $ ) {

	affiliates_dashboard_overview_graph.render = function( container_id, legend_container_id, hits, visits, referrals, amounts_by_currency, span, ticks, dates ) {

		var datasets = [];
		datasets['hits'] = [
			{
				label : affiliates_dashboard_overview_graph_l12n.hits,
				data : hits,
				lines : { show : true, lineWidth : 1 },
				yaxis : 1,
				color : '#1761ff'
			},
			{
				data : span,
				lines : { show : false },
				yaxis : 1
			}
		];
		datasets['visits'] = [
			{
				label : affiliates_dashboard_overview_graph_l12n.visits,
				data : visits,
				lines : { show : true, lineWidth : 1 },
				yaxis : 1,
				color : '#ff9e17'
			},
			{
				data : span,
				lines : { show : false },
				yaxis : 1
			}
		];
		datasets['referrals'] = [
			{
				label : affiliates_dashboard_overview_graph_l12n.referrals,
				data : referrals,
				color : '#179e17',
				bars : { align : 'center', show : true, barWidth : 1 },
				hoverable : true,
				yaxis : 1
			},
			{
				data : span,
				lines : { show : false },
				yaxis : 1
			}
		];

		var legend = [
			{ label : affiliates_dashboard_overview_graph_l12n.hits, color : '#1761ff', dataset : 'hits' },
			{ label : affiliates_dashboard_overview_graph_l12n.visits, color : '#ff9e17', dataset : 'visits' },
			{ label : affiliates_dashboard_overview_graph_l12n.referrals, color : '#179e17', dataset : 'referrals' }
		];

		var initial_dataset = null;

		for ( var currency_id in amounts_by_currency ) {
			datasets[currency_id] = [
				{
					label : currency_id,
					data : amounts_by_currency[currency_id],
					color : '#616161',
					lines : { show : true, lineWidth : 1 },
					yaxis : 1
				},
				{
					data : span,
					lines : { show : false },
					yaxis : 1
				}
			];
			legend.push( { label : currency_id, color : '#616161', dataset : currency_id } );
			if ( initial_dataset === null ) {
				initial_dataset = currency_id;
			}
		}

		var options = {
			xaxis : { ticks : ticks },
			yaxis : { min : 0, tickDecimals : 0 },
			yaxes : [ {} ],
			grid : { hoverable : true },
			legend : { show : false }
		};

		$.plot( $( "#" + container_id ), datasets[initial_dataset], options );

		for ( var i = 0; i < legend.length; i++ ) {
			var dataset = legend[i].dataset;
			$( '#' + legend_container_id ).append(
				'<div id="legend-item-' + dataset + '" class="legend-item" data-dataset="' + dataset + '">' +
				'<span class="legend-item-color" style="background-color:' + legend[i].color + '"></span>' +
				'<span class="legend-item-label">' + legend[i].label + '</span>' +
				'</div>'
			);
			if ( dataset === initial_dataset ) {
				$( '#legend-item-' + dataset ).addClass( 'active' );
			}
			$( '#legend-item-' + dataset ).on( 'hover', function( event ) {
				$( '.legend-item' ).removeClass( 'active' );
				$.plot( $( "#" + container_id ), datasets[$(this).data('dataset')], options );
				$( this ).addClass( 'active' );
			} );
		}

		var tooltipItem = null;
		var statsDates = dates;
		$( "#" + container_id ).bind( "plothover", function ( event, pos, item ) {
			if ( item ) {
				if ( tooltipItem === null || item.dataIndex != tooltipItem.dataIndex || item.seriesIndex != tooltipItem.seriesIndex ) {
					tooltipItem = item;
					$( "#tooltip" ).remove();
					var x = item.datapoint[0];
						y = item.datapoint[1];
					affiliates_dashboard_overview_graph.tooltip(
						item.pageX,
						item.pageY,
						item.series.label + " : " + y +  '<br/>' + statsDates[x] 
					);
				}
			} else {
				$( "#tooltip" ).remove();
				tooltipItem = null;
			}
		} );

	};

	affiliates_dashboard_overview_graph.tooltip = function ( x, y, contents ) {
		var tooltip = $( '<div id="tooltip">' + contents + '</div>' ).css( {
			position: 'absolute',
			display: 'none',
			top: y + 5,
			left: x + 5,
			border: '1px solid #333',
			'border-radius' : '4px',
			padding: '6px',
			'background-color': '#ccc',
			opacity: 0.90
		} ).appendTo( "body" ).fadeIn( 200 );
		if ( tooltip.position().left >= tooltip.parent().width() / 2 ) {
			tooltip.css( { left : x - tooltip.outerWidth() } );
		}
	};

} )( jQuery );
