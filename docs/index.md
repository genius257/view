## Introduction

View is a simple solution for bringing JSX elment like syntax into PHP.

Details for class behaviors in the links below:

[Views](view.md)

[Components](component.md)

## Getting started

1. require `genius257/view` with composer.
2. Create a new view file with wanted content.
    1. If component tags are wanted, these should be made and referenced at this step.
3. In another php file, create a new View class instance.
    1. Single parameter being the relative opr absolute path to the viewfile.
4. Call render on the view instance and output the return of the method with for example echo.

That's it! your PHP files should now putput processed html.

## View files

View files are normal HTML/PHP files that MAY contain component tags

see **Component tags** section on the [Components](component.md) page for details.
