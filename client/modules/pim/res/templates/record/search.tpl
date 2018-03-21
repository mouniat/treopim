
<div class="row search-row">
    <div class="form-group col-md-7 col-sm-6">
        <div class="input-group">
            <div class="input-group-btn left-dropdown{{#unless leftDropdown}} hidden{{/unless}}">
                <button type="button" class="btn btn-default dropdown-toggle filters-button" title="{{translate 'Filter'}}" data-toggle="dropdown" tabindex="-1">
                    <span class="filters-label"></span>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu pull-left filter-menu">

                    <li><a class="preset" tabindex="-1" href="javascript:" data-name="" data-action="selectPreset"><div>{{translate 'All'}}</div></a></li>
                    {{#each presetFilterList}}
                        <li><a class="preset" tabindex="-1" href="javascript:" data-name="{{name}}" data-action="selectPreset"><div>{{#if label}}{{label}}{{else}}{{translate name category='presetFilters' scope=../../entityType}}{{/if}}</div></a></li>
                    {{/each}}
                    <li class="divider preset-control hidden"></li>


                    <li class="preset-control remove-preset hidden"><a tabindex="-1" href="javascript:" data-action="removePreset">{{translate 'Remove Filter'}}</a></li>
                    <li class="preset-control save-preset hidden"><a tabindex="-1" href="javascript:" data-action="savePreset">{{translate 'Save Filter'}}</a></li>
                    {{#if boolFilterListLength}}
                        <li class="divider"></li>
                    {{/if}}

                    {{#each boolFilterListComplex}}
                        <li class="checkbox{{#if hidden}} hidden{{/if}}"><label><input type="checkbox" data-role="boolFilterCheckbox" name="{{name}}" {{#ifPropEquals ../bool name true}}checked{{/ifPropEquals}}> {{translate name scope=../entityType category='boolFilters'}}</label></li>
                    {{/each}}
                </ul>
            </div>
            {{#unless textFilterDisabled}}<input type="text" class="form-control text-filter" name="textFilter" value="{{textFilter}}" tabindex="1">{{/unless}}
            <div class="input-group-btn">
                <button type="button" class="btn btn-primary search btn-icon" data-action="search">
                    <span class="glyphicon glyphicon-search"></span>
                </button>
            </div>
        </div>
    </div>
    <div class="form-group col-md-5 col-sm-6">
        <div class="btn-group search-right-buttons-group">
            <div class="btn-group">
                <button type="button" class="btn btn-default" data-action="reset">
                    <span class="glyphicon glyphicon-repeat"></span>&nbsp;{{translate 'Reset'}}
                </button>
                <button type="button" class="btn btn-default dropdown-toggle add-filter-button" data-toggle="dropdown" tabindex="-1">
                    {{translate 'Add Field'}} <span class="caret"></span>
                </button>
                <ul class="dropdown-menu pull-right filter-list">
                    {{#each advancedFields}}
                        <li data-name="{{name}}" class="{{#if checked}}hide{{/if}}"><a href="javascript:" class="add-filter" data-action="addFilter" data-name="{{name}}">{{translate name scope=../entityType category='fields'}}</a></li>
                    {{/each}}
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="advanced-filters-bar" style="margin-bottom: 12px;"></div>
<div class="row advanced-filters hidden" style=" display: flex; flex-wrap: wrap;">
    <div class="filter-applying-condition text-center hidden col-xs-12">{{{translate "filterApplyingCondition" scope="Search" category="messages"}}}</div>
    {{#each filterDataList}}
    <div class="filter filter-{{name}} col-sm-4 col-md-3" data-name="{{name}}">
        {{{var key ../this}}}
    </div>
    {{/each}}
</div>

