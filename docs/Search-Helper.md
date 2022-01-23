## Search Helper

Helper provides an easy way to render the search form based on search parameters.

### Search Helper methods

* ```controls(PlumSearch\FormParameter\ParameterRegistry $parameters, $options = []):``` Returns all registered parameters options in Form::inputs format.
* ```control(PlumSearch\FormParameter\BaseParameter $parameter, $options = []):``` Returns single parameter options in Form::input format. Default parameter settings could be overwritten by the second parameter.
* ```postRender(PlumSearch\FormParameter\ParameterRegistry $parameters, $options = []): string``` For each parameter checks and calls postRenderCallback(PlumSearch\FormParameter\BaseParameter $parameter, \Cake\View\View $view), which supposed to perform post processing of rendered data for example using javascript logic.

### Search element

Using ```PlumSearch.search``` element is the fastest way to render search form.

* **formOptions:** Search form options array.
* **inputOptions:** Overloaded inputs options array. It uses parameter names as a key for additional input options.
* **searchParameters:** This variable is configured by the Filter component when the prg method is called. It contains a ParameterRegistry instance.

