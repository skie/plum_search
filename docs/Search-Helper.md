## Search Helper

Helper provides easy way to render search form based on search parameters.

### Search Helper methods

* **inputs(PlumSearch\FormParameter\ParameterRegistry $parameters, $options = []):** Returns all registered parameters options in Form::inputs format.
* **input(PlumSearch\FormParameter\BaseParameter $parameter, $options = []):** Returns single parameter options in Form::input format. Default parameter settings could be overwritten by second parameter.

### Search element

Using ```PlumSearch.search``` element is the fastest way to render search form.

* **formOptions:** Search form options array.
* **inputOptions:** Overloaded inputs options array. It uses parameter names as key for additional input options.
* **searchParameters:** This variable configured by Filter component when prg method is called. It contains ParameterRegistry instance.

