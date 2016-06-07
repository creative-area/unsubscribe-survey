# Unsubscribe Form Survey

> Exit interview to capture the reasons why subscribers are leaving after they unsubscribe

![screenshot](screenshot.png?raw=true)

## Features

- Fully customizable (texts, questions, logo, ...)
- Responses are stored in a log file (CSV format)
- Aggregated stats are stored in a JSON file
- Display results as a pie chart
- Optionally receive an email when someone fill the form

## Usage

Just put those files on a server and expose the `htdocs` directory to the web.

**Unsubscribe Form Survey** comes with a default configuration that contains the most common questions you may want to ask to your (leaving) subscribers.

You can **override the default configuration** by creating a `config.custom.php` file that should contain a `$custom_config` array with the same structure as the `$default_config` array of the `config.default.php` file.

To **display the pie chart with the results**, request your form with the following query string: `?stats=[your-stats-secret]` (`secret` by default).

A **JSON file** (`unsubscribe.json`) that constains the aggregated stats and a **log file** (`unsubscribe.log`) that stores all the responses will be created in the `data` directory at first call, so be sure that your webserver has write access to this directory.

To receive a **mail notification** each time a user unsubscribes, simply fill the `email_to` option in your `$custom_config`.
