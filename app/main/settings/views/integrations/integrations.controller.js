(function ()
{
    'use strict';

    angular
            .module('app.settings')
            .controller('IntegrationsController', IntegrationsController);
            

    /** @ngInject */
    function IntegrationsController($document, $window, $timeout, $mdDialog,$stateParams,sessionService,$http,$scope,$state,notifyService)
    {


            $scope.company_id = sessionService.get('company_id');
            var originatorEv;
            var vm = this ;

            vm.ssActivewearDialog = ssActivewearDialog ;
            vm.authorizeNet = authorizeNet ;
            vm.upsDialog = upsDialog ;
            vm.quickbookActivewearDialog=quickbookActivewearDialog;
            vm.quickbookDisconnect = quickbookDisconnect;

            vm.openMenu = function ($mdOpenMenu, ev) {
                originatorEv = ev;
                $mdOpenMenu(ev);
            };

            $scope.GetAllApi = function ()
            {
                $http.get('api/public/admin/company/getSnsAPI/'+$scope.company_id).success(function(result) 
                {   
                    if(result.data.success=='1')
                    {
                        $scope.sns = result.data.data[0];
                    }
                    else
                    {
                        notifyService.notify('error',result.data.message);
                    }
                    $("#ajax_loader").hide();
                });
                 $http.get('api/public/admin/company/getAuthorizeAPI/'+$scope.company_id).success(function(result) 
                {   
                    if(result.data.success=='1')
                    {
                        $scope.authorize = result.data.data[0];
                    }
                    else
                    {
                        notifyService.notify('error',result.data.message);
                    }
                    $("#ajax_loader").hide();
                });
                $http.get('api/public/admin/company/getUpsAPI/'+$scope.company_id).success(function(result) 
                {   
                    if(result.data.success=='1')
                    {
                        $scope.ups = result.data.data[0];
                    }
                    else
                    {
                        notifyService.notify('error',result.data.message);
                    }
                    $("#ajax_loader").hide();
                });
            }

            $scope.GetAllApi();

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
            
            function ssActivewearDialog(ev, settings)
            {
                $("#ajax_loader").show();
                $mdDialog.show({
                    controller: function ($scope,params)
                    {
                        $scope.params = params;
                        $scope.sns = $scope.params.sns;
                         $("#ajax_loader").hide();

                        $scope.closeDialog = function() 
                        {
                            $mdDialog.hide();
                        } 
                        $scope.UpdateTableField = function(field_name,field_value,table_name,cond_value)
                        {
                            var vm = this;
                            var UpdateArray = {};
                            UpdateArray.table =table_name;
                            
                            $scope.name_filed = field_name;
                            var obj = {};
                            obj[$scope.name_filed] =  field_value;
                            UpdateArray.data = angular.copy(obj);
                            UpdateArray.cond=  {id:cond_value};

                            $http.post('api/public/common/UpdateTableRecords',UpdateArray).success(function(result) {
                            if(result.data.success=='1')
                            {
                                notifyService.notify('success',result.data.message);   
                            }
                            else
                            {
                                notifyService.notify('error',result.data.message);
                            }
                           });
                        }

                    },
                    controllerAs: 'vm',
                    templateUrl: 'app/main/settings/dialogs/ssActivewear/ssActivewear-dialog.html',
                    parent: angular.element($document.body),
                    targetEvent: ev,
                    clickOutsideToClose: true,
                    locals: {
                        params:$scope,
                        event: ev
                    }
                });
            }


             function quickbookActivewearDialog(ev, settings)
            {


                  $http.get('api/public/qbo/qboConnect').success(function(result, status, headers, config) 
              {
                 
                  
              });
            }

            function quickbookDisconnect(ev, settings)
            {


                  $http.get('api/public/qbo/disconnect').success(function(result, status, headers, config) 
              {
                 $state.reload();
                  
              });
            }


            

            function authorizeNet(ev, settings)
            {
                $("#ajax_loader").show();
                $mdDialog.show({
                    controller: function ($scope,params)
                    {
                        $scope.params = params;
                        $scope.authorize = $scope.params.authorize;
                       $("#ajax_loader").hide();
                        $scope.closeDialog = function() 
                        {
                            $mdDialog.hide();
                        } 
                        $scope.UpdateTableField = function(field_name,field_value,table_name,cond_value)
                        {
                            var vm = this;
                            var UpdateArray = {};
                            UpdateArray.table =table_name;
                            
                            $scope.name_filed = field_name;
                            var obj = {};
                            obj[$scope.name_filed] =  field_value;
                            UpdateArray.data = angular.copy(obj);
                            UpdateArray.cond=  {id:cond_value};

                            $http.post('api/public/common/UpdateTableRecords',UpdateArray).success(function(result) {
                            if(result.data.success=='1')
                            {
                                notifyService.notify('success',result.data.message);   
                            }
                            else
                            {
                                notifyService.notify('error',result.data.message);
                            }
                           });
                        }

                    },
                    controllerAs: 'vm',
                    templateUrl: 'app/main/settings/dialogs/authorizeNet/authorizeNet-dialog.html',
                    parent: angular.element($document.body),
                    targetEvent: ev,
                    clickOutsideToClose: true,
                    locals: {
                        params:$scope,
                        event: ev
                    }
                });
            }

            function upsDialog(ev, settings)
            {
                $("#ajax_loader").show();
                $mdDialog.show({
                    controller: function ($scope,params)
                    {
                        $scope.params = params;
                        $scope.ups = $scope.params.ups;
                        $("#ajax_loader").hide();
                        $scope.closeDialog = function() 
                        {
                            $mdDialog.hide();
                        } 
                        $scope.UpdateTableField = function(field_name,field_value,table_name,cond_value)
                        {
                            var vm = this;
                            var UpdateArray = {};
                            UpdateArray.table =table_name;
                            
                            $scope.name_filed = field_name;
                            var obj = {};
                            obj[$scope.name_filed] =  field_value;
                            UpdateArray.data = angular.copy(obj);
                            UpdateArray.cond=  {id:cond_value};

                            $http.post('api/public/common/UpdateTableRecords',UpdateArray).success(function(result) {
                            if(result.data.success=='1')
                            {
                                notifyService.notify('success',result.data.message);   
                            }
                            else
                            {
                                notifyService.notify('error',result.data.message);
                            }
                           });
                        }


                    },
                    controllerAs: 'vm',
                    templateUrl: 'app/main/settings/dialogs/ups/ups-dialog.html',
                    parent: angular.element($document.body),
                    targetEvent: ev,
                    clickOutsideToClose: true,
                    locals: {
                        params:$scope,
                        event: ev
                    }
                });
            }
        
    }


       
})();
