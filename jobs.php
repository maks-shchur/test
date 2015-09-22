<div class="main-body" ng-controller="searchController">
    <form class="form-search" action="/jobs/search" method="get" ng-submit="onSubmitSearch($event)">
        <div class="bloc-search">
            <input type="text" id="search_input"
                   name="job[text_query]"
                   placeholder="<?php echo $_locale['Enter keywords']; ?>" 
                   autocomplete="off"
                   ng-model="search.text" 
                   ng-class="{'search-input-active': autocomplete[0]}" 
                   ng-change="changeSearchInput()" 
                   ng-keyup="keyPressAutoComplete($event)"
            >
            <input type="hidden" name="job[query]" value="{{search.query}}" ng-keyup="keyPressSearchInputHidden($event)">
        </div>
        <div class="link-example-search"><?php echo $_locale['For example: Programmer, Accountant']; ?></div>
        <!--<a href="#" class="link-advanced-search">+ <?php echo $_locale['advanced search']; ?></a>-->
        <div id="select_search_sity" class="select-div-search-city"></div>
        <input id="form_search_input_city_hidden" type="hidden" name="job[city]" value="" >

        <a href="#" class="link-search-submit" ng-click="searchJobs($event)"><?php echo $_locale['find vacansion']; ?></a>
        <input type="submit" id="submit_search_jobs" value="Submit" hidden>
        <div class="autocomplete-bloc" ng-show="autocomplete[0]">
            <ul>
                <li ng-repeat="item in autocomplete" ng-click="clickAutocompleteItem(item)">
                    <div class="item-countainer" ng-class="{'active': item.active == true}">{{item.word}}</div>
                </li>
            </ul>
        </div>
    </form>
    
    <!-- Микроразметка хлебние крошки -->
    <?php $this->getBox('home/breadcrumbs', ['breadcrumbs' => $this->breadcrumbs]); ?>
    <!-- Кінець блоку микроразметка хлебние крошки -->
   
    <!-- Пошук за розділами -->
    <div class="search-jobs-bloc">
        <?php $this->getBox('jobs/catalogBloc', ['limit' => 16]); ?>
        <div class="footer-panel">
            <a href="/jobs/by-catalog" class="link-all">
                <?php echo $_locale['all sections']; ?>
                <div class="arrow-right-icon"></div>
            </a>
        </div>
    </div>
    <!-- Пошук за містами -->
    <?php $this->getBox('home/topCityBloc'); ?>
    <!-- Пошук за професіями -->
    
    <div class="search-jobs-bloc">
        <?php $this->getBox('jobs/professionsBloc'); ?>
    </div>
</div>