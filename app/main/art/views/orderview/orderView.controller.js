(function ()
{
    'use strict';

    angular
            .module('app.art')
            .controller('orderViewController', orderViewController);

    /** @ngInject */
    function orderViewController($document,  $state,$window, $timeout, $mdDialog, $stateParams,$resource,sessionService,$scope,$http,notifyService,AllConstant,$filter)
    {
        var vm = this;
        vm.createNewScreen = createNewScreen;
        vm.generateArtForm = generateArtForm;
        $scope.company_id = sessionService.get('company_id');
        $scope.order_id = $stateParams.id;

        var misc_list_data = {};
        var condition_obj = {};
        condition_obj['company_id'] =  sessionService.get('company_id');
        misc_list_data.cond = angular.copy(condition_obj);

        $http.post('api/public/common/getAllMiscDataWithoutBlank',misc_list_data).success(function(result, status, headers, config) {
                $scope.miscData = result.data.records;
        });
        
        $scope.updateOrderStatus = function(name,value,id)
        {
            var order_main_data = {};

            order_main_data.table ='orders';

            $scope.name_filed = name;
            var obj = {};
            obj[$scope.name_filed] =  value;
            order_main_data.data = angular.copy(obj);

            var condition_obj = {};
            condition_obj['id'] =  id;
            order_main_data.cond = angular.copy(condition_obj);

            $http.post('api/public/common/UpdateTableRecords',order_main_data).success(function(result) {

                var data = {"status": "success", "message": "Data Updated Successfully."}
                notifyService.notify(data.status, data.message);
            });
        }

        $scope.GetOrderScreenSet = function() 
        {
            $("#ajax_loader").show();
            var GetScreenArray = {company_id:$scope.company_id, order_id:$scope.order_id};
            $http.post('api/public/art/ScreenSets',GetScreenArray).success(function(result) 
            {
                if(result.data.success == '1') 
                {
                    $scope.ScreenSets = result.data.records;
                    $scope.ScreenSets_new = 
                    {
                        data_all: result.data.records,
                        selected: null,
                    };


                }
                else
                {
                    notifyService.notify('error',result.data.message);
                    $state.go('app.art');
                    return false;
                }
                $("#ajax_loader").hide();
            });
        }
        $scope.GetOrderScreenSet();
    
        $scope.change_sort = function ()
        {
            $("#ajax_loader").show();
            $http.post('api/public/art/change_sortscreen',$scope.ScreenSets_new.data_all).success(function(result) 
            {
                $scope.GetOrderScreenSet();
            });
        }



        function createNewScreen(ev, position_id) {

            $mdDialog.show({
                controller: function ($scope, params,position_id)
                            {
                                //alert(position_id);
                                $scope.params = params;
                                $http.get('api/public/art/GetScreenset_detail/'+position_id).success(function(result) 
                                {
                                    if(result.data.success == '1') 
                                    {
                                        $scope.details_screenset = result.data.records;
                                        $scope.getColors = result.data.getColors;
                                        $scope.screen_allcolors = result.data.allcolors;
                                        $scope.simulateQuery = false;
                                        $scope.isDisabled    = false;
                                        $scope.states        = loadAll();
                                        $scope.querySearch   = querySearch;
                                    }
                                    else
                                    {
                                        notifyService.notify('error',result.data.message);
                                    }
                                });
                                function querySearch (query) 
                                {
                                    var results = query ? $scope.states.filter( createFilterFor(query) ) : $scope.states, deferred;
                                    if ($scope.simulateQuery) 
                                    {
                                        deferred = $q.defer();
                                        $timeout(function () { deferred.resolve( results ); }, Math.random() * 1000, false);
                                        return deferred.promise;
                                    } 
                                    else 
                                    {
                                        return results;
                                    }
                                }
                                function loadAll() 
                                {
                                    var allStates = $scope.screen_allcolors;
                                    return allStates;
                                }
                                function createFilterFor(query) 
                                {
                                    var lowercaseQuery = angular.lowercase(query);
                                    // console.log(lowercaseQuery);
                                    return function filterFn(state) 
                                    {
                                        return (state.name.indexOf(lowercaseQuery) === 0);
                                    };
                                }
                                $scope.closeDialog = function() 
                                {
                                    $mdDialog.hide();
                                } 
                                $scope.initial_add_color = [];
                                $scope.add_color = function(id,color_name)
                                {
                                    if( !angular.isUndefined(id))
                                    {
                                        $('#remove_color').val('');
                                        $scope.initial_add_color.push({id:id,color_name:color_name});
                                    }
                                }
                                $scope.CreateScreenset = function(alldata)
                                {
                                    alldata = {alldata:alldata,add_screen_color:$scope.initial_add_color,remove_screen_color:$scope.screen_id_removed};
                                    
                                    $http.post('api/public/art/create_screen',alldata).success(function(result) 
                                    {
                                        if(result.data.success == '1') 
                                        {
                                            $scope.closeDialog();
                                            notifyService.notify('success','Screenset Updated successfully.');
                                        }
                                        else
                                        {
                                            notifyService.notify('error',result.data.message);
                                        }
                                    });
                                
                                }
                                $scope.remove_added = function(index)
                                {
                                    $scope.initial_add_color.splice(index,1);
                                }

                                $scope.screen_id_removed = [];
                                $scope.remove_selected = function(index,id)
                                {
                                    if( !angular.isUndefined(id))
                                    {
                                        $scope.getColors.splice(index,1);
                                        $scope.screen_id_removed.push({id:id});
                                    }
                                }
                    },
                controllerAs: 'vm',
                templateUrl: 'app/main/art/dialogs/createScreen/createScreen-dialog.html',
                parent: angular.element($document.body),
                targetEvent: ev,
                clickOutsideToClose: true,
                locals: {
                    params:$scope,
                    position_id:position_id,
                    event: ev
                },
                onRemoving : $scope.GetOrderScreenSet
            });
        }
        function generateArtForm(ev, settings) {
            $mdDialog.show({
                 controller: function ($scope, params){
                            $scope.closeDialog = function() 
                            {
                                $mdDialog.hide();
                            } 
                    },
                controllerAs: 'vm',
                templateUrl: 'app/main/art/dialogs/generateArtForm/generateArtForm-dialog.html',
                parent: angular.element($document.body),
                targetEvent: ev,
                clickOutsideToClose: true,
                locals: {
                    params:$scope,
                    event: ev
                }
            });
        }
        // Datatable Options
       
        var originatorEv;
        vm.openMenu = function ($mdOpenMenu, ev) {
            originatorEv = ev;
            $mdOpenMenu(ev);
        };
        vm.dtInstanceCB = dtInstanceCB;
        //methods
        function dtInstanceCB(dt) {
            var datatableObj = dt.DataTable;
            vm.tableInstance = datatableObj;
        }
        $scope.printPdf=function(mail)
        {
            
            var pass_array = {order_id:$scope.order_id,company_id:$scope.company_id,mail:mail}
            if(mail=='1')
            {
                var k = confirm("Do you want to send Art approval PDF to client?");
                if(k==false)
                {
                    return false;
                }
            }
            var target;
            var form = document.createElement("form");
            form.action = 'api/public/art/ArtApprovalPDF';
            form.method = 'post';
            form.target = target || "_blank";
            form.style.display = 'none';

            var input_screenset = document.createElement('input');
            input_screenset.name = 'art';
            input_screenset.setAttribute('value', JSON.stringify(pass_array));
            form.appendChild(input_screenset);

            document.body.appendChild(form);
            form.submit();  
        };

        $scope.UpdateTableField = function(field_value,order_id)
        {
            var vm = this;
            var UpdateArray = {};
            UpdateArray.table ='art';
            UpdateArray.data = {approval:field_value};
            UpdateArray.cond = {order_id:order_id};
            $http.post('api/public/common/UpdateTableRecords',UpdateArray).success(function(result) 
            {
                if(result.data.success=='1')
                {
                    notifyService.notify('success', result.data.message);
                }
                else
                {
                    notifyService.notify('error', result.data.message);
                }
            });
        }

    }
})();
