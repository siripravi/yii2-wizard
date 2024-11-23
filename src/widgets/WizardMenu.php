<?php

namespace siripravi\wizard\widgets;

use yii\widgets\Menu;
use siripravi\wizard\assets\WizardWidgetAsset;

/**
 * WizardMenu class.
 * Creates a menu from the wizard steps.
 */
class WizardMenu extends Menu
{
    /**
     * @var string widget html id
     */
    public $id = 'wizard';
    public $options = ['id' => 'progressbar'];

    /**
     * @var array default button configuration
     */
    public $default_buttons = [
        'prev' => ['title' => 'Previous', 'options' => ['class' => 'btn btn-default', 'type' => 'button']],
        'next' => ['title' => 'Next', 'options' => ['class' => 'btn btn-default', 'type' => 'button']],
        'save' => ['title' => 'Save', 'options' => ['class' => 'btn btn-default', 'type' => 'button']],
        'skip' => ['title' => 'Skip', 'options' => ['class' => 'btn btn-default', 'type' => 'button']],
    ];
    /**
     * @var string The CSS class for the current step
     */
    public $currentStepCssClass = 'current-step';
    /**
     * @var array The item to be shown to indicate completion of the wizard.
     * e.g. ['label' => 'Done', 'url' => null]
     */
    public $finalItem;
    /**
     * @var string The CSS class for future steps
     */
    public $futureStepCssClass = 'future-step';
    /**
     * @var string The CSS class for past steps
     */
    public $pastStepCssClass = 'past-step';
    /**
     * @var string The current step
     */
    public $step;
    /**
    
     */
    public $wizard;

    /**
     * Initialise the widget
     */
    public function init()
    {
        WizardWidgetAsset::register($this->getView());
        $route  = ['/' . $this->wizard->owner->route];
        $params = $this->wizard->owner->actionParams;
        $steps  = $this->wizard->steps;
        $index  = array_search($this->step, $steps);

        foreach ($steps as $step) {
            $stepIndex = array_search($step, $steps);
            $params[$this->wizard->queryParam] = $step;

            if ($stepIndex == $index) {
                $active = true;
                $class  = $this->currentStepCssClass;
                $url    = array_merge($route, $params);
            } elseif ($stepIndex < $index) {
                $active = false;
                $class  = $this->pastStepCssClass;
                $url    = ($this->wizard->forwardOnly
                    ? null : array_merge($route, $params)
                );
            } else {
                $active = false;
                $class  = $this->futureStepCssClass;
                $url    = null;
            }

            $this->items[] = [
                'label'   => $this->wizard->stepLabel($step),
                'url'     => $url,
                'active'  => $active,
                'options' => compact('class')
            ];

            if (!empty($this->finalItem)) {
                $this->items[] = $this->finalItem;
            }
        }
    }

    public function runs()
    {
        parent::run();
        // Wizard line calculation
        $step_count = count($this->items) + ($this->complete_content ? 1 : 0);
        $wizard_line_distribution = round(100 / $step_count); // Percentage
        $wizard_line_width = round(100 - $wizard_line_distribution); // Percentage
        $wizard_line = '';

        $tab_content = '';

        // Navigation tracker
        end($this->items);
        $last_id = key($this->items);

        $first = true;
        $class = '';

        foreach ($this->items as $id => $step) {

            // Current or fist step is active, next steps are inactive (previous steps are available)
            if ($id == $this->start_step or (is_null($this->start_step) && $class == '')) {
                $class = 'active';
            } elseif ($class == 'active') {
                $class = 'disabled';
            }

            // Add icons to the wizard line
            $wizard_line .= Html::tag(
                'li',
                Html::a('<span class="round-tab"><i class="' . $step['icon'] . '"></i></span>', '#step' . $id, [
                    'data-toggle' => 'tab',
                    'aria-controls' => 'step' . $id,
                    'role' => 'tab',
                    'title' => $step['title'],
                ]),
                array_merge(
                    [
                        'role' => 'presentation',
                        'class' => $class,
                        'style' => ['width' => $wizard_line_distribution . '%']
                    ],
                    isset($step['options']) ? $step['options'] : []
                )
            );

            // Setup tab content
            $tab_content .= '<div class="tab-pane ' . $class . '" role="tabpanel" id="step' . $id . '">';
            $tab_content .= $step['content'];

            // Setup navigation buttons
            $buttons = [];
            $button_id = "{$this->id}_step{$id}_";
            if (!$first) {
                // Show previous button except on first step
                $buttons[] = $this->navButton('prev', $step, $button_id);
            }
            if (array_key_exists('skippable', $step) and $step['skippable'] === true) {
                // Show skip button if specified
                $buttons[] = $this->navButton('skip', $step, $button_id);
            }
            if ($id == $last_id) {
                // Show save button on last step
                $buttons[] = $this->navButton('save', $step, $button_id);
            } else {
                // On all previous steps show next button
                $buttons[] = $this->navButton('next', $step, $button_id);
            }
            // Add buttons to tab content
            $tab_content .= Html::ul($buttons, ['class' => 'list-inline pull-right', 'encode' => false]);

            // Finish tab
            $tab_content .= '</div>';

            $first = false;
        }

        // Add a completed step if specified
        if ($this->complete_content) {
            $class = 'disabled';

            // Check if completed tab is set as start_step
            if ($this->start_step == 'completed') {
                $class = 'active';
            }

            // Add completed icon to wizard line
            $wizard_line .= Html::tag(
                'li',
                Html::a('<span class="round-tab"><i class="glyphicon glyphicon-ok"></i></span>', '#complete', [
                    'data-toggle' => 'tab',
                    'aria-controls' => 'complete',
                    'role' => 'tab',
                    'title' => 'Complete',
                ]),
                [
                    'role' => 'presentation',
                    'class' => $class,
                    'style' => ['width' => $wizard_line_distribution . '%']
                ]
            );

            $tab_content .= '<div class="tab-pane ' . $class . '" role="tabpanel" id="complete">' . $this->complete_content . '</div>';
        }

        // Start widget
        echo '<div class="wizard" id="' . $this->id . '">';

        // Render the steps line
        echo '<div class="wizard-inner"><div class="connecting-line" style="width:' . $wizard_line_width . '%"></div>';
        echo '<ul class="nav nav-tabs" role="tablist">' . $wizard_line . '</ul>';
        echo '</div>';

        // Render the content tabs
        echo '<div class="tab-content">' . $tab_content . '</div>';

        // Finalize the content tabs
        echo '<div class="clearfix"></div>';

        // Finish widget
        echo '</div>';
    }

    /**
     * Generate navigation button
     *
     * @param string $button_type prev|skip|next\save
     * @param array $step step configuration
     * @param string $button_id
     *
     * @return string
     */
    protected function navButton($button_type, $step, $button_id)
    {
        // Setup a unique button id
        $options = ['id' => $button_id . $button_type];

        // Apply default button configuration if defined
        if (isset($this->default_buttons[$button_type]['options'])) {
            $options = array_merge($options, $this->default_buttons[$button_type]['options']);
        }

        // Apply step specific button configuration if defined
        if (isset($step['buttons'][$button_type]['options'])) {
            $options = array_merge($options, $step['buttons'][$button_type]['options']);
        }

        // Add navigation class
        if ($button_type == 'prev') {
            $options['class'] = $options['class'] . ' prev-step';
        } else {
            $options['class'] = $options['class'] . ' next-step';
        }

        // Display button
        if (isset($step['buttons'][$button_type]['html'])) {
            return $step['buttons'][$button_type]['html'];
        } elseif (isset($step['buttons'][$button_type]['title'])) {
            return Html::button($step['buttons'][$button_type]['title'], $options);
        } else {
            return Html::button($this->default_buttons[$button_type]['title'], $options);
        }
    }
}
