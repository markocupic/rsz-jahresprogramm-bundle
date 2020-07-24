/**
 * Created by Marko on 29.01.2016.
 */

(function ($) {
    $(document).ready(function () {


        $('.all-events .col_5').addClass('hidden');
        $('.all-events .col_6').addClass('hidden');
        $('.jahresprogramm-dropdown-btn .checkbox input[value="col_1"]').prop('checked', true);
        $('.jahresprogramm-dropdown-btn .checkbox input[value="col_2"]').prop('checked', true);
        $('.jahresprogramm-dropdown-btn .checkbox input[value="col_3"]').prop('checked', true);
        $('.jahresprogramm-dropdown-btn .checkbox input[value="col_4"]').prop('checked', true);
        $('.jahresprogramm-dropdown-btn .checkbox input[value="col_5"]').prop('checked', false);
        $('.jahresprogramm-dropdown-btn .checkbox input[value="col_6"]').prop('checked', false);


        $('.jahresprogramm-dropdown-btn .checkbox input').on('click', function () {
            $('.all-events .' + $(this).val()).toggleClass('hidden');
        });

        $('#jahresprogramm_search_input').keyup(function () {
            searchTable($(this).val());
        });

        /**
         *
         * @param inputVal
         */
        function searchTable(inputVal) {
            var table = $('#table_all_events');
            table.find('tr').each(function (index, row) {
                var allCells = $(row).find('td');
                if (allCells.length > 0) {
                    var found = false;
                    allCells.each(function (index, td) {
                        var regExp = new RegExp(inputVal, 'i');
                        if (regExp.test($(td).text())) {
                            found = true;
                            return false;
                        }
                    });
                    if (found == true)$(row).show(); else $(row).hide();
                }
            });
        }

        // Init tablesorter
        $("#table_all_events").tablesorter({
            headers: {
                0: {
                    sorter: false
                }
            }
        });
    });
})(jQuery);

