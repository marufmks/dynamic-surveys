# Dynamic Surveys for WordPress

A WordPress plugin that allows administrators to create simple surveys and dynamically display aggregated results to users.

## Description

Dynamic Surveys is a lightweight yet powerful WordPress plugin that enables site administrators to create and manage surveys easily. Users can participate in surveys, and results are displayed in real-time using beautiful pie charts.

### Features

- Easy survey creation with multiple options
- Real-time results display using pie charts
- Shortcode support for embedding surveys anywhere
- User-based voting system to prevent duplicate votes
- Survey status management (open/closed)
- Mobile-responsive design
- Copy shortcode with one click
- Toast notifications for better user experience

## Installation

1. Download the plugin zip file
2. Go to WordPress admin panel > Plugins > Add New
3. Click "Upload Plugin" and choose the downloaded zip file
4. Click "Install Now"
5. After installation, click "Activate Plugin"

## Usage

### Creating a Survey

1. In WordPress admin, go to Tools > Dynamic Surveys
2. Fill in the survey details:
   - Survey Title
   - Question
   - Add at least two options (click "Add Option" for more)
3. Click "Create Survey"

### Managing Surveys

From the Existing Surveys table, you can:
- Copy the shortcode by clicking on it
- Delete surveys
- Toggle survey status (open/closed)

### Embedding Surveys

1. Copy the shortcode for your survey (e.g., `[dynamic_survey id="1"]`)
2. Paste it into any post or page where you want the survey to appear

### Viewing Results

- After voting, users will automatically see the results in a pie chart
- Results update in real-time as more users vote

## Requirements

- WordPress 5.2 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

## Frequently Asked Questions

**Q: Can users vote multiple times?**
A: No, the plugin tracks votes by user ID and prevents duplicate voting.

**Q: Can I customize the appearance of the surveys?**
A: Yes, you can customize the appearance using CSS. The plugin includes basic styling that works with most themes.

**Q: What happens when a survey is closed?**
A: When a survey is closed, users will see a message indicating that the survey is no longer accepting votes.

## Support

For support, please visit the [plugin's GitHub repository](https://github.com/marufmks/dynamic-survey) or create a support ticket.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### 1.0.0
- Initial release
- Basic survey creation and management
- Real-time results display
- User vote tracking
- Shortcode support
- Mobile-responsive design
- Toast notifications
- Copy shortcode functionality

## Credits

- Uses [Chart.js](https://www.chartjs.org/) for pie charts
- Uses [Toastr](https://github.com/CodeSeven/toastr) for notifications

## Author

Maruf Khan
- [GitHub](https://github.com/marufmks) 