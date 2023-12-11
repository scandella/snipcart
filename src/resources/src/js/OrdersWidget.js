/* global Craft */
/* global Garnish */
/* global jQuery */

import ApexCharts from 'apexcharts'

(function($) {
    Craft.OrdersWidget = Garnish.Base.extend({
        settings: null,
        $widget: null,
        $chartContainer: null,

        init: function(widgetId) {
            const widgetSelector = '#widget' + widgetId;
            this.$widget = $(widgetSelector);
            this.$chartContainer = this.$widget.find('.orders-chart');
            this.updateChart();
        },

        getYMax: function(series) {
            let max = 0;

            series.forEach(function(row) {
                const data = row.data;
                const rowMax = Math.max(...data)
                if (rowMax > max) {
                    max = rowMax;
                }
            });

            // round up to nearest 5
            const resolution = 5;
            max = Math.round((max+resolution/2)/resolution) * resolution;

            return max;
        },

        updateChart: function() {
            this.$chartContainer.addClass('spinner');
            const self = this;

            Craft.postActionRequest(
                'snipcart/charts/get-orders-data',
                {
                    type: this.$chartContainer.data('chart-type'),
                    range: this.$chartContainer.data('chart-range'),
                },
                $.proxy(function(response, textStatus) {
                    // TODO: gracefully handle error
                    self.$chartContainer.removeClass('spinner');

                    if (textStatus === 'success' && typeof (response.error) === 'undefined') {
                        const options = {
                            chart: {
                                fontFamily: "system-ui, BlinkMacSystemFont, -apple-system, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif",
                                height: 200,
                                type: 'bar',
                                toolbar: { show: false },
                                animations: { speed: 350 }
                            },
                            colors: ['#0d78f2'],
                            series: response.series,
                            dataLabels: {
                                enabled: false,
                                //offsetY: -12,
                            },
                            plotOptions: {
                                bar: {
                                    dataLabels: { position: 'top' }
                                }
                            },
                            xaxis: {
                                categories: response.columns,
                                position: 'bottom',
                                labels: {
                                    rotate: 0,
                                    offsetY: 1,
                                    show: response.columns.length < 15,
                                    formatter: function (val) {
                                        if (val === undefined) {
                                            return val;
                                        }
                                        const datePieces = val.split('-'); // YYYY-MM-DD
                                        const month = parseInt(datePieces[1]);
                                        const day = parseInt(datePieces[2]);
                                        return `${month}/${day}`;
                                    }
                                },
                                axisBorder: { show: false },
                                axisTicks: {
                                    show: true,
                                    height: 3,
                                    color: '#e3e5e8',
                                },
                            },
                            yaxis: {
                                min: 0,
                                max: self.getYMax(response.series),
                                tickAmount: 5,
                                forceNiceScale: true,
                                axisBorder: { show: false },
                                axisTicks: { show: false },
                                labels: {
                                    show: true,
                                    offsetX: -22,
                                    style: {
                                        color: '#8f98a3',
                                    },
                                    formatter: function(val) {
                                        if (response.formats.currencySymbol !== undefined) {
                                            return response.formats.currencySymbol + val;
                                        }

                                        return val;
                                    }
                                }
                            },
                            tooltip: {
                                enabled: true,
                                x: { show: true },
                                y: { show: false, },
                            },
                            grid: {
                                borderColor: '#e3e5e8',
                                strokeDashArray: 1,
                                padding: {
                                    left: -10,
                                    right: 0,
                                    top: 0,
                                    bottom: 0
                                },
                            },
                            stroke: {
                                show: true,
                                curve: 'straight',
                                lineCap: 'round',
                            },
                            legend: { show: false }
                        }
                    
                        var chart = new ApexCharts(
                            self.$chartContainer[0],
                            options
                        );

                        chart.render();
                
                    }
                })
            );
        },
    });
})(jQuery);