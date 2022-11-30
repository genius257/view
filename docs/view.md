# Views

The view class takes the relative or absolute view file path as first argument in the contructor.

To output the processed view file content, simply call render and output the return via something like echo.

## Exceptions

The view class will modify the exception stack trace, to provide details for the estimated view and component content traces, not tracked by PHP.

This gives better clarity for tracking a problem in the code, but breaks the standard stack trace output format, to give deatiled view/render trace information.
