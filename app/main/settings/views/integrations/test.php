    <div class="content order-info-page m-b-20">
        <div class="simple-table-container md-whiteframe-4dp">
            <div class="table-title paddingbottom-0" layout="row" layout-align="space-between center">
                <div class="table-title-text h2 text-semibold" flex=80>
                    <div class="title" layout-padding><span class="basicInfoStyle">3RD PARTY SOFTWARE INTEGRATIONS</span></div>
                </div>
            </div>

            <div class="ms-responsive-table-wrapper priceGridTableContainer userMgmntPadding">
                <table class="table3 row-border hover" id="integrations" dt-options="vm.dtOptions"  >
                    <tbody class="" >
                        <tr class="cursor-p">
                            <td class="integrationsTd1"><img src="assets/images/integrations/QuickBooks.png" height="105px" width="105px" /></td>
                            <td class="integrationsTdWidth integrationsTd2">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean euismod bibendum laoreet. Proin gravida dolor sit amet lacus accumsan et viverra justo commodo. Proin sodales pulvinar tempor. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam fermentum, nulla luctus pharetra vulputate, felis tellus mollis orci, sed rhoncus sapien nunc eget odio.
                            </td>

                          <!--   <td class=""><md-button class="btn-width-200 connectToQuickBooksBtn" ng-click="vm.quickbookActivewearDialog($event)"><img src="assets/images/integrations/connectToQuickBooks.png" class="" /></md-button></td>  -->


                      

                       <?php
                        $qbo_obj = new \App\Http\Controllers\QuickBookController();
                        $qbo_connect = $qbo_obj->qboConnect();
                        ?>
                        @if(!$qbo_connect)
                        <td class=""><connect-to-quickbooks /><ipp:connectToIntuit></ipp:connectToIntuit> </td> 
                        @else
                        <a href="{{url('qbo/disconnect')}}" title="">Disconnect</a>
                        @endif

                          
                        </tr>
                        <tr class="cursor-p">
                            <td class="integrationsTd1"><img src="assets/images/integrations/s_sActiveWear.png" height="105px" width="105px" /></td>
                            <td class="integrationsTdWidth integrationsTd2">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean euismod bibendum laoreet. Proin gravida dolor sit amet lacus accumsan et viverra justo commodo. Proin sodales pulvinar tempor. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam fermentum, nulla luctus pharetra vulputate, felis tellus mollis orci, sed rhoncus sapien nunc eget odio.
                            </td>

                            <td class=""><md-button ng-class="{'md-raised btn-width-200 md-button md-teal-theme md-ink-ripple':sns.username=='', 'md-raised btn-width-200 md-accent md-hue-1 md-button md-teal-theme md-ink-ripple':sns.username!=''}"  ng-click="vm.ssActivewearDialog($event)">CONNECT</md-button></td>
                        </tr>
                        <tr class="cursor-p">
                            <td class="integrationsTd1"><img src="assets/images/integrations/authorize_net.png" height="105px" width="105px" /></td>
                            <td class="integrationsTdWidth integrationsTd2">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean euismod bibendum laoreet. Proin gravida dolor sit amet lacus accumsan et viverra justo commodo. Proin sodales pulvinar tempor. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam fermentum, nulla luctus pharetra vulputate, felis tellus mollis orci, sed rhoncus sapien nunc eget odio.
                            </td>   
                  <td class=""><md-button  ng-class="{'md-raised btn-width-200 md-button md-teal-theme md-ink-ripple':authorize.login=='', 'md-raised btn-width-200 md-accent md-hue-1 md-button md-teal-theme md-ink-ripple':authorize.login!=''}"  ng-click="vm.authorizeNet($event)">CONNECT</md-button></td>
                        </tr>
                        <tr class="cursor-p">
                            <td class="integrationsTd1"><img src="assets/images/integrations/ups.png" height="105px" width="105px" /></td>
                            <td class="integrationsTdWidth integrationsTd2">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean euismod bibendum laoreet. Proin gravida dolor sit amet lacus accumsan et viverra justo commodo. Proin sodales pulvinar tempor. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam fermentum, nulla luctus pharetra vulputate, felis tellus mollis orci, sed rhoncus sapien nunc eget odio.
                            </td>

                            <td class=""><md-button ng-class="{'md-raised btn-width-200 md-button md-teal-theme md-ink-ripple':ups.username=='', 'md-raised btn-width-200 md-accent md-hue-1 md-button md-teal-theme md-ink-ripple':ups.username!=''}"  ng-click="vm.upsDialog($event)">CONNECT</md-button></td>
                        </tr>
                    </tbody> 
                </table>
         
            </div>
        </div>
    </div>