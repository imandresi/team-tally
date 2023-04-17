
# DEVELOPMENT PROCESS

Building the TEAM TALLY WordPress plugin was a challenging and rewarding experience that allowed me to use my skills in WordPress development and plugin architecture. Throughout the development process, I encountered various obstacles that demanded creative problem-solving, resourcefulness, and meticulous attention to detail.

This section of the documentation is intended for those interested in an overview of my development journey in creating the plugin. In addition to the process, I will also be discussing the tools and technologies that I utilized.

By sharing my development journey, I aim to demonstrate my capacity to handle intricate tasks, operate independently, and produce high-quality code that satisfies the client's requirements. Thank you for taking the time to review this documentation, and I look forward to discussing my experience with you.

## Project requirements

Create a custom WordPress plugin that will make it possible to store football/sports teams categorized by the league they are from and list them with a custom-made Elementor widget.

- Every team should have a name, history, logo, and a nickname
- Leagues should support adding a logo
- Elementor widget should make it possible to query the teams by keyword, by number, by league and change their basic styling (color, font styling)

## Project Study
According to the requirements, the main role of the plugin is to provide a **database management of teams and leagues** which will allow the user to create leagues, and to create teams associated with them.

Those users should then be able to create web pages using Elementor and incorporate those data so that web visitors could browse them.

If I put myself in the visitor's place I would need to view a list of the leagues. I would also want to know which teams compose each league. At first, I would just need a list of all the teams with just the minimum information such as Team name, nickname. But next, I may need the ability to view the full detail of a specific team.

It then appears that we need the following features:

### Basic features
- An Elementor widget that can display all the *available leagues* or a specific league.
- Another Elementor widget that can display a *list of teams* based on a query filter that can be defined by the user. The query filter could be based on parameters such as the team ID, league ID, or a specific keyword.
  - The `team_id` filter would allow displaying data for a particular team.
  - The `league_id` filter would enable displaying a list of teams that belong to a specific league.
  - The `keyword` filter would allow displaying a list of teams that contain a particular keyword. This feature would be particularly useful when integrated into a search engine.
- A *pagination* system to handle large query results for teams.

### Dynamic query feature
Now let's suppose I had created a page displaying a table listing of 20 teams from a particular league. Next, I want the click on each team's name to display their full info. **It may appear that I would have to create ==20 pages== displaying full team detail for each team** and then link each of them to the table listing. And what if there are 40, 60, ... 100 teams ? It would be a very exhaustive task.   

We have to find a solution in order to facilitate that process. And one that appears is the use of *HTTP Query Parameter* to transmit or to extract the `team_id` or the `league_id` value to an Elementor Widget. So, instead of creating 100 pages of teams detail, we just have to create ==one page== which is linked with that HTTP Query Parameter.

### Rows ordering feature
Additionally, the user may also want to have the list of teams or leagues sorted by a specific field. It would then also be useful to add *row ordering* feature

### Custom template feature
We talked earlier about the necessity to display two types of pages about a team:
- a list of teams belonging to a league
- a full detail of a specific team

An Elementor Widget is used to display teams data. But as we need two different results, do we need to build one widget for each type of output ? And what if the user needs another kind of output ? And another ?

The solution, is to associate a **custom template feature** to the team and league widget so that the user can customize the output according to their needs.

The plugin user will create its own template by entering HTML code. 

### Custom CSS feature
HTML templates are incomplete without CSS. Therefore, it's essential to provide users with the ability to create custom CSS for their templates. By doing so, users will be able to customize the look and feel of their website to match their desired style and branding.

Adding this feature to the TEAM TALLY plugin would enable users to create unique templates and customize their CSS styles without requiring advanced coding skills. 

### Import / Export feature
The Import feature is primarily designed to help users quickly get up and running with the plugin by offering the option to import demo data for testing purposes. This feature can be particularly useful for users who are new to the plugin or those who want to test different scenarios without having to manually create each data entry. 

Additionally, the Export feature can allow users to save their data for backup or to transfer their data to another website.

### Summary of final Features :
- LEAGUE AND TEAM MANAGEMENT feature that enables the creation, modification, and deletion of leagues and teams.
- ELEMENTOR WIDGETS:
  - **Team Elementor Widget** with league filtering, team filtering, keyword filtering, rows ordering, and pagination features.
  - **League Elementor Widget** with league filtering and rows ordering feature.
  - **Dynamic HTTP query** feature allowing the use of HTTP query parameter for `team_id` or `league_id` and transmit them to the Elementor Widget to display dynamically their content on a web page. This avoids the necessity of creating multiple pages.
  - **Custom Templates** feature that allows users to create unique HTML templates for their teams and leagues display.
  - **Custom CSS** feature that enables users to customize the look and feel of their templates by creating their CSS styles.
- IMPORT/EXPORT feature to quickly import demo data for testing and to make backups

These features will enable users to create and manage their teams and leagues more effectively, while also providing a customizable interface for displaying that information on their website.

## Finding a plugin name
I didn't spend much time on finding a name for the plugin. I just submitted the requirement features to ChatGPT and asked it to give me a name for the plugin. I chose the first name on the list: TEAM TALLY

But as the project progressed, I realized that the name did not accurately reflect the plugin's features since it did not include a **tally of team points**. Despite this, I chose to keep the name as that feature could be considered a possible improvement for future updates to the plugin.

## Building the directory structure
The directory structure is a crucial part on every web development project as it keeps the code organized, making it easier to write and maintain.

In any project, I always make sure to include an `asset` directory where I store all media, CSS, JS, fonts, and plugin files. 

As per this project, the first structure of the directory typically follows the format below:

```
plugins
└── team-tally
    ├── assets
    │   ├── css
    │   ├── images
    │   └── js
    └── team-tally.php
```

The remaining structure now depends on other factors. And we will update it progressively throughout this document.

## Using a CSS Preprocessor
I find CSS preprocessors like SASS to be incredibly valuable in any web project. SASS, in particular, has helped me to organize my code by separating each feature into multiple files, keeping them modularized.

One of the features I find amazing in SASS is the nested rule feature. It follows the structure of the HTML, which makes reading the code a lot easier. With regular CSS, multiple selectors nested in CSS can quickly become a mess that's difficult to read.

But that's not all - SASS also offers other cool features like variables and mixins that help me to avoid code duplication and make the code easier to debug.

Another great advantage of SASS is its ability to group all the CSS files into a single one. This helps to improve performance by avoiding the browser having to download multiple CSS files.

## Using WebPack
In my project, I utilized WebPack to perform several tasks, including:

- Compiling SCSS into CSS using fast-sass-loader
- Minifying the final production CSS for faster loading performance
- Combining multiple JavaScript files into a single bundle to improve loading performance
- Isolating the JavaScript environment from the global environment using WebPack's module approach

The source folder for WebPack is located in `./src`, which contains all the JS and SCSS source code.

The compiled files are outputted to the previously specified `./assets` folder.

```
plugins
└── team-tally
    ├── assets
    │   ├── css
    │   ├── images
    │   └── js
    ├── src
    │   ├── scss
    │   └── js
    └── team-tally.php
```

## Using Git
After configuring those initial setup, I initiate Git to start monitoring changes in the project.

This allows me to keep a record of all changes made to the code, as well as revert back to a previous version if needed.

## Home-made boilerplate
The most crucial part of the project is building a plugin boilerplate that will serve as the foundation. Although there are several WordPress plugin boilerplate available online, I decided to build one from scratch that meets my specific requirements. These requirements include:

- Isolation from WordPress environment via namespaces
- Object-oriented approach
- Easy implementation of the singleton pattern
- Use of the MVC design pattern
- Use of autoloader
- Implementation of a templating engine supporting escaped/unescaped template variables as well as PHP code
- Separation of frontend and admin code 

By meeting these requirements, the code will be more maintainable and extensible.

Below is the updated directory structure:

```
plugins
└── team-tally
    ├── assets
    │   ├── css
    │   ├── images
    │   └── js
    ├── includes
    │   ├── controllers
    │   ├── core
    │   ├── languages
    │   ├── models
    │   ├── system 
    │   ├── templates 
    │   ├── views 
    │   └── bootstrap.php
    ├── src
    │   ├── scss
    │   └── js
    └── team-tally.php
```

### Singleton Pattern
Some classes may use the **Singleton Pattern**. If applicable, instead of implementing it manually we just have to extend our class with the Singleton class from `./system/singleton.php`. Next, we implement the `init()` method which is called automatically when then class is first initialized. 

### Model / View / Controller Pattern

The **model** is a class which contains all WordPress and low-level database interactions such as the use of `WP_Query`, `get_posts`, `get_post_meta`, `get_terms`, etc... 

The **controller** is a class which contains logic, form processing etc.

The **view** is a class which contains codes that are in charge of output display. It initializes the parameters to be sent to the templates.

The **templates** contain the HTML code defining the final output. It's template variables are displayed using the braces format.

- `{{..}}` is used for escaped output
- `{{{...}}}` is used for unescaped output
- The use of PHP is supported for possible extensive customization. When using PHP, template variables are accessible as normal PHP variables.

## Customizing the admin menu
<table>
    <tr>
        <td>TECHNOLOGY</td>
        <td>
           <code>$submenu</code>,
           <code>add_menu_page()</code>,
           <code>add_submenu_page()</code>
        </td>
    </tr>
    <tr>
        <td>ACTION HOOKS</td>
        <td>
          <code>admin_menu</code>,
          <code>parent_file</code>,
          <code>submenu_file</code>
        </td>
    </tr>
    <tr>
        <td>FILES</td>
        <td>
            <code>./includes/core/admin/admin_menu.php</code><br>
        </td>
    </tr>
</table>

When I customized the admin menu to add the TEAM TALLY menu and submenus, I had to select a custom icon for the main menu to make it look prettier. Since there was no predefined dashicon for a football icon, I decided to use an SVG football icon to achieve the best output rendering. This has to be achieved by injecting the SVG content as a base64 data in `add_menu_page()` through a custom function reading the SVG file.

In order to be more user-friendly, I also had to change the label of the "team listing" submenu according to the selected league. The team listing menu is closely linked to a league. So when a league is selected, the label of the "Team Listing" submenu had to be changed into "Teams in {league name}". Similarly, when no league is selected, the menu should be made unavailable.

After setting up the TEAM TALLY menu, I noticed that the corresponding active menu was not highlighted when the list of teams in a specific league are displayed. Upon debugging, I discovered that the problem was due to the links to the team listing which had to be suffixed with `&league_id=...`. That prevented the activation of the default selection highlighting behavior of WordPress. However, after a deep dive into the WordPress code, I managed to solve the problem by adding some customization code through the `parent_file` and `submenu_file` hook filter.

## Implementation of the *League management*
<table>
    <tr>
        <td>TECHNOLOGY</td>
        <td>Taxonomy, Term Meta, Nonces, Admin Notices</td>
    </tr>
    <tr>
        <td>ACTION HOOKS</td>
        <td><code>init</code></td>
    </tr>
    <tr>
        <td>FILES</td>
        <td>
            <code>./includes/controllers/leagues_controller.php</code><br>
            <code>./includes/models/leagues_model.php</code><br>
            <code>./includes/views/leagues_view.php</code><br>
            <code>./templates/leagues/add_edit_league.php</code><br>
            <code>./templates/leagues/league_item.php</code><br>
            <code>./templates/leagues/list_leagues.php</code>
        </td>
    </tr>
</table>

The responsibilities of the league management module include:
- displaying a form for creating or editing a league
- displaying a list of all the leagues, where each league has a link to access modifications, deletions, and team management
- deleting a specific league.

I have a funny story about this module. Initially, I implemented its features using "Custom Post Types" instead of "Taxonomy". However, when I started working on the Team management module, I realized that I could not attach a league to a team using custom post types, as taxonomy is more suitable for that.

Luckily, thanks to the adopted MVC structure, fixing the problem was as simple as changing the model to use "Taxonomy" instead of "Custom Post types".

## Implementation of the *Team management*

<table>
    <tr>
        <td>TECHNOLOGY</td>
        <td>Custom Post Types, Post Meta, Post Term, Meta Boxes, Quick Edit, Nonces, Admin Notices, <code>WP_Posts_List_Table</code></td>
    </tr>
    <tr>
        <td>ACTION HOOKS</td>
        <td>
          <code>init</code>,
          <code>admin_head-edit.php</code>,
          <code>quick_edit_custom_box</code>,
          <code>manage_{$post_type}_posts_custom_column</code>,
          <code>edit_form_after_title</code>,
          <code>admin_head-post-new.php</code>,
          <code>admin_head-post.php</code>,
          <code>add_meta_boxes_{$post_type}</code>,
          <code>wp_insert_post_empty_content</code>,
          <code>save_post_{$post_type}</code>,
        </td>
    </tr>
    <tr>
        <td>FILTER HOOKS</td>
        <td>
          <code>admin_body_class</code>,
          <code>views_edit-{$post_type}</code>,
          <code>wp_list_table_class_name</code>,
          <code>manage_{$post_type}_posts_columns</code>,
          <code>enter_title_here</code>,
          <code>redirect_post_location</code>,
          <code>wp_insert_post_empty_content</code>,
        </td>
    </tr>
    <tr>
        <td>FILES</td>
        <td>
            <code>./includes/controllers/teams_controller.php</code><br>
            <code>./includes/controllers/teams_edit_controller.php</code><br>
            <code>./includes/controllers/teams_list_controller.php</code><br>
            <code>./includes/core/admin/teams_list_table.php</code><br>
            <code>./includes/models/teams_model.php</code><br>
            <code>./includes/views/teams_view.php</code><br>
            <code>./includes/templates/teams/edit_form.php</code><br>
            <code>./includes/templates/teams/meta_box_league.php</code><br>
        </td>
    </tr>
</table>

### List of teams
Displaying the list of teams is different from displaying the list of available posts of a custom post type. Here, each team is associated with a league, so it's better for the user to see a list of teams from a specific league selected from the list of leagues.

Therefore, the entry point for the "Teams Management" is always the list of leagues. After selecting a team from a particular league, the user is brought to the corresponding list of teams. Below are the list of customization:

- The interface of the "Teams Management" is customized to remind the user that it's the interface of a specific league. The **admin submenu** is changed to include the name of the selected league, and the **title of the list of teams** is also changed to include the selected league.
- Additionally, due to the mandatory association of a league with each team, all other post actions, including edit, delete, bulk actions, and filter, have been customized accordingly.
- The post list view for teams has been customized to include additional columns for #ID, Nickname, History, and Logo.

I used a customized `WP_Posts_List_Table` class to present and manage a list of teams for a particular league.

### Edit a team
Building the interface which allows us to create or edit a team requires us to create **custom field** for the Nickname and a **meta box** for associating a league to it. The logo is managed using post thumbnail.


## Elementor Widgets
The league and team Elementor widgets have several common features, which have been implemented as PHP traits to avoid duplicating code. Additionally, both widgets share a common set of Javascript code that manages the custom template field features. This Javascript code communicates with the PHP code of the plugin using AJAX.

### Implementation for the *League Elementor Widget*
<table>
    <tr>
        <td>TECHNOLOGY</td>
        <td>Traits, JSON, XML, Ajax, <code>\Elementor\Widget_Base</code></td>
    </tr>
    <tr>
        <td>ACTION HOOKS</td>
        <td>
          <code>elementor/init</code>,
          <code>elementor/elements/categories_registered</code>,
          <code>elementor/widgets/register</code>,
          <code>elementor/controls/register</code>,
          <code>elementor/preview/enqueue_styles</code>,
          <code>elementor/frontend/before_enqueue_styles</code>,
          <code>elementor/editor/after_enqueue_scripts</code>,
          <code>wp_ajax_{$action}</code>,
          <code>wp_ajax_no_priv_{$action}</code>
        </td>
    </tr>
    <tr>
        <td>ELEMENTOR JS HOOKS</td>
        <td>
          <code>panel/open_editor/widget/${WIDGET_NAME}</code>
        </td>
    </tr>
    <tr>
        <td>FILES</td>
        <td>
            <code>./includes/elementor/widgets/elementor_league_listing_widget.php</code><br>
            <code>./includes/elementor/widgets/elementor_manager.php</code><br>
            <code>./includes/elementor/widgets/elementor_widget_trait.php</code><br>
            <code>./includes/elementor/models/template_base_model_abstract.php</code><br>
            <code>./includes/elementor/models/league_listing_template_model.php</code><br>
            <code>./includes/elementor/models/league_template_init.xml</code><br>
            <code>./includes/elementor/models/league_template.xml</code><br>
        </td>
    </tr>
</table>

### Implementation for the *Team Elementor Widget*
<table>
    <tr>
        <td>TECHNOLOGY</td>
        <td>Traits, JSON, XML, Ajax, <code>\Elementor\Widget_Base</code></td>
    </tr>
    <tr>
        <td>ACTION HOOKS</td>
        <td>
          <code>elementor/init</code>,
          <code>elementor/elements/categories_registered</code>,
          <code>elementor/widgets/register</code>,
          <code>elementor/controls/register</code>,
          <code>elementor/preview/enqueue_styles</code>,
          <code>elementor/frontend/before_enqueue_styles</code>,
          <code>elementor/editor/after_enqueue_scripts</code>,
          <code>wp_ajax_{$action}</code>,
          <code>wp_ajax_no_priv_{$action}</code>
        </td>
    </tr>
    <tr>
        <td>ELEMENTOR JS HOOKS</td>
        <td>
          <code>panel/open_editor/widget/${WIDGET_NAME}</code>
        </td>
    </tr>
    <tr>
        <td>FILES</td>
        <td>
            <code>./includes/elementor/widgets/elementor_team_listing_widget.php</code><br>
            <code>./includes/elementor/widgets/elementor_manager.php</code><br>
            <code>./includes/elementor/widgets/elementor_widget_trait.php</code><br>
            <code>./includes/elementor/models/template_base_model_abstract.php</code><br>
            <code>./includes/elementor/models/team_listing_template_model.php</code><br>
            <code>./includes/elementor/models/team_template_init.xml</code><br>
            <code>./includes/elementor/models/team_template.xml</code><br>
        </td>
    </tr>
</table>

### Custom Templates
The custom templates consist of a "container template" and an "item template" that contain HTML codes with the option to use "template variables". Each template have a name, and they are stored in an XML file. The widgets come with default templates that cannot be altered.

The "Manage Output Template" section of each Elementor widget has four controls: the "Use template" combobox, and the "Template Name", "Template Container", and "Template item" fields for modifying a template. When the section is opened, the "Use template" control is updated with AJAX. Any changes made to the "Template Container" or "Template item" field are automatically saved to XML after a few seconds of inactivity.

### Custom CSS
The custom CSS styles are applied on the custom templates during the rendering of the Elementor widget.  This is achieved by embedding the custom styles with the final HTML output.

## Import / Export
<table>
    <tr>
        <td>TECHNOLOGY</td>
        <td>JSON, ZIP, Admin Notices, Nonces</td>
    </tr>
    <tr>
        <td>ACTION HOOKS</td>
        <td>
          <code>init</code>
        </td>
    </tr>
    <tr>
        <td>FILES</td>
        <td>
            <code>./includes/controllers/export_controller.php</code><br>
            <code>./includes/controllers/import_controller.php</code><br>
            <code>./includes/core/admin/plugin_data.php</code><br>
        </td>
    </tr>
</table>

When the data is exported, all leagues, teams and their corresponding logos are stored as XML files inside a temporary folder of the WordPress `upload` folder. That folder is then renamed and is given the name `teamtally_data_export{$suffix}.zip` where the `{$suffix}` part is the date of exportation.

The **demo** data which can be imported to test the plugin is just a ZIP file coming from that export process. It is stored inside the `./_demo/` folder.

When the data is imported, the ZIP file is unzipped inside a temporary folder of the WordPress `upload` folder and the extracted JSON files are inserted inside the database while logos are inserted into the media library.

## Possible improvements to bring to the project 
I believe that an improvement that could be added to the TEAM TALLY plugin would be to include a scoring system for each team, making the plugin more in line with its name. Additionally, we could also make it more extensible by adding action and filter hooks, and provide users with an easy way to add additional fields for each league or team.
