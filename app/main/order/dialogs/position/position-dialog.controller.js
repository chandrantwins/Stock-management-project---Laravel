(function ()
{
    'use strict';

    angular
        .module('app.order')
        .controller('PositionDialogController', PositionDialogController);
/** @ngInject */
    function PositionDialogController(order_id,$stateParams,$scope, $mdDialog, $document, $mdSidenav, DTOptionsBuilder, DTColumnBuilder,$resource,$http,notifyService,$state,sessionService)
    {
            var misc_list_data = {};
            var condition_obj = {};
            condition_obj['company_id'] =  sessionService.get('company_id');
            misc_list_data.cond = angular.copy(condition_obj);

            $http.post('api/public/common/getAllMiscDataWithoutBlank',misc_list_data).success(function(result, status, headers, config) {
                      $scope.miscData = result.data.records;
            });

            $scope.GetValue = function (fruit) {
                var fruitId = $scope.order_design_position.position_id;
                var fruitName = $.grep($scope.miscData, function (fruit) {
                    return fruit.id == fruitId;
                })[0].value;
                $window.alert("Selected Value: " + fruitId + "\nSelected Text: " + fruitName);
            }




                 $scope.save = function (positionData) {
 
         
                   if(positionData == undefined) {

                      var data = {"status": "error", "message": "Position and Quantity should not be blank"}
                              notifyService.notify(data.status, data.message);
                              return false;
                    } else if(positionData.qnty == undefined) {

                      var data = {"status": "error", "message": "Quantity should not be blank"}
                              notifyService.notify(data.status, data.message);
                              return false;
                    } else if(positionData.position_id == undefined) {

                      var data = {"status": "error", "message": "Position should not be blank"}
                              notifyService.notify(data.status, data.message);
                              return false;
                    }

              var combine_array_id = {};
              var position_id = positionData.position_id
             
              combine_array_id.positionData = positionData;
              combine_array_id.design_id = $stateParams.id;
              combine_array_id.order_id = order_id;
              combine_array_id.position = $scope.miscData.position[position_id].value;

              
 
              $http.post('api/public/order/addPosition',combine_array_id).success(function(result) 
                {

                   if(result.data.success == '2') 

                        {
                       
                             var data = {"status": "error", "message": "This position is already exists in this order."}
                             notifyService.notify(data.status, data.message);
                             return false;
                        } 


                    $mdDialog.hide();
                   
                });
        };

        $scope.cancel = function () {
            $mdDialog.hide();
        };
    }
})();