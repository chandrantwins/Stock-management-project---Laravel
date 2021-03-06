(function ()
{
    'use strict';

    angular
        .module('app.invoices')
        .controller('linktoPayController', linktoPayController);

    /** @ngInject */
    function linktoPayController(result, $mdDialog,$controller,$state,  event,$scope,sessionService,$resource)
    {
        var vm = this;
        $scope.attn = result.data.session_link;
        if(result.data.session_another_link){
            $scope.attn12 = result.data.session_another_link; 
        }
       
       this.userState = '';

        this.states = ('AL AK AZ AR CA CO CT DE FL GA HI ID IL IN IA KS KY LA ME MD MA MI MN MS ' +
            'MO MT NE NV NH NJ NM NY NC ND OH OK OR PA RI SC SD TN TX UT VT VA WA WV WI ' +
            'WY').split(' ').map(function (state) { return { abbrev: state }; });


        this.affilliatePriceGrid = '';

        this.priceGrids = ('Price_Grid_1 Price_Grid_2 Price_Grid_3').split(' ').map(function (state) { return { abbrev1: state }; });
        
        $scope.cancel = function () {
            $mdDialog.hide();
        };

        
        /**
         * Close dialog
         */
        function closeDialog()
        {
            $mdDialog.hide();
        }
    }
})();