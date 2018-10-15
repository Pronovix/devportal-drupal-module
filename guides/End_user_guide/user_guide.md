#  Documentation

Prepared by  |  Pronovix
-----------  |  --------
             |  István Zoltán Szabó, Senior Technical Writer
             |  Pozsár Anett, Junior Technical Writer
Date:        |  28.09.2018 (v1.0)
Updated on:  |

## Goal of this document
This document is an onboarding material for content editors and site administrators. It provides a summary about the actions that are necessary to manage and maintain the  developer portal.

The document is for content managers and site maintainers: programming skills are not necessary to execute the actions covered in the document.

## Login

Site users are able to log in on: [SITE URL] with the Username/email address and password combination.
Click _Log in_.

__!NOTE__: The password field is case sensitive.

## Managing landing pages

 developer portal uses the paragraph toolkit to create landing pages. Content creators can use the toolkit to build sophisticated, tailor made pages in a couple of hours without the help from developers or site builders.

Paragraphs are landing page building elements with a set of adjustable features such as title, description, picture, or background color. When you build a landing page with the paragraph toolkit, you can set up the paragraphs individually without coding skills.

### Creating a new landing page

You can create a new landing page type content item by following the steps below, or edit an existing one by only editing some of the fields.

1. In the administrative menu, go to _Content_.

2. Click on _Add content_, then click on _Landing page_.

3. Fill in the form fields where needed. See the table for fields and values below.

   Required fields are marked with a red .

| Field name | Explanation  | Example value  |
|---|---|---|
| Title (required field)  | The title of the landing page content item. This is a required field.  | Our services; Get a quote  |
| Text  | You don't need to provide any information here as the paragraph elements will contain the page content. Leave this field empty.  | -  |
| Background image  | You don't need to upload any images here as the paragraph elements will contain the page visuals. Leave this field empty.  | -  |
| Landing page elements  | A list about the existing paragraph items on the page. This list is empty until you add a paragraph item to the landing page.  | [Grid]  |
| Add Block / Add Grid / Add CTA  |  You can choose and add paragraphs to your landing page. See the detailed description in the [Paragraph Types](#paragraph-types) section. | [CTA]  |
| 🙿 Published  | If the checkbox is checked, the landing page will be published after _Save_.  | -  |
|  Status  |  It contains a basic status report about the landing page: date of the last save and name of the author. Add information about your edits for other editors, reviewers and publishers in the _Revision log message_ field.  |  Not saved yet  |
|  Menu settings |  You can add the landing page to the menu as menu item. You can add the menu link title, its description, its position in the menu and the weight of the menu item. | -  |
|  Book outline | You can put the landing page into a hierarchical book structure. You can either create a new book or add the page to an existing book. If you choose an existing book, you can define the position of the page in the hierarchy.  | -  |
| URL path settings  | URL alias will be generated automatically by default. Uncheck the _Generate automatic URL alias_ checkbox if you want to create custom URL alias and provide the custom URL in the _URL alias_ field.  | /about-us   |
| Authoring information  | Change the author (Authored by) and publishing date (Authored on), if you are not creating your own landing page or would like to change or adjust the preset date of publication.  | - |
| Promotion options  |  No need to change anything here.| - |

### Adding a New Paragraph to a Landing page

To add a new paragraph to your landing page, click on _Add Block_ or _Add Grid_ or _Add CTA_ (depends on what you want to add).

![Add a new landing page element](@guide_path/Assets/1_lpe.jpg)

You are able to edit your chosen paragraph type on the appearing interface.
You can add as many paragraphs as you want.

Click _Preview_ on the bottom of the page to check what your content items will look like on the landing page. Click _Save_ to create your landing page or save your changes.

### Editing, Removing, or Re-ordering Existing Paragraphs

#### Editing

You can edit the existing Paragraph type items on a landing page by following the steps below.

1. In the administrative menu, go to _Content_. Click on the title of the landing page you want to edit (e.g. Test landing page). The landing page will open.

    ![Landing page editing](@guide_path/Assets/2_editing.jpg)

2. To edit the landing page, click _Edit_ on the top left.

3. To edit an existing paragraph, choose the paragraph from the landing page element list and click _Edit_. Modify the content or the settings of the chosen paragraph. If you want to close an opened paragraph, click _Collapse_ on the top of the paragraph type form.

4. _Save_.

#### Removing

You can remove existing paragraph type items from a landing page by following the steps below:

__Step 1__ and __Step 2__ are the same as above.

3. To remove an existing paragraph, choose the paragraph from the landing page element list and select _Remove_ from the drop-down menu at the end of the row. Click _Confirm removal_ if you want to remove the paragraph. Select _Restore_ if you don’t want to remove the paragraph.

4. _Save_.

#### Re-ordering

You can change the order of the existing paragraph items on a landing page by following the steps below:

__Step 1__ and __Step 2__ are the same as above.

3. To change the order of the paragraphs, drag and drop the paragraph item to the right place with the arrow at the beginning of the row of the paragraph.

4. _Save_.

![Re-ordering paragraphs](@guide_path/Assets/5_reorder.jpg)

### Paragraph types

#### Block

With the Block paragraph, you can display a block that is available in the Drupal site on your landing page. For the available blocks, check _Structure_ > _Block layout_.

To add a _Block_ paragraph type to your landing page:

1. Click the _Add Block_ button under the landing page element list.

2. Select the block you want to display on the landing page from the drop-down menu.

3. _Save_ or add another paragraph.

#### Grid

To add a _Grid_ paragraph type to your landing page:

1. Click the _Add Grid_ button under the landing page element list.

2. __Grid title__: This is the title of the Grid paragraph item that will be displayed on the landing page.

3. __Grid columns__: You can set the number of grid columns that should be displayed.

4. __Grid column ratios__: Optionally, you can specify the ratio of the columns. The column ratio is set 1 by default, the size of the columns are equal this way. For example, if you have 3 columns and every column has the column ratio to 1, then every column will take one third of the full page size. However, if you set the column ratio of one of the columns to 2 (A column: 2; B column: 1; C column: 1), then this column takes two fourth of the page, when the other two column take one fourth part of it each.

5. __Background color__: Select the background color of the grid paragraph and set the opacity.

6. __Grid elements__: You can add elements to the Grid paragraph items. Choose the element type you want to add and fill out the appearing form.

##### Card

To add a Card element to the Grid paragraph item:

1. Click on _Add Card_ under the Grid elements list.

2. __Description__: A short description that will be displayed on the landing page. You can choose different text formats ( style, Basic HTML, Restricted HTML, Full HTML, GitHub Flavored Markdown) from the drop-down menu.

3. __Target__: The URL that opens when the user clicks on the Grid card.

4. __Background image__: Upload a background image. Click _Choose file_, locate the image on your computer, the system will upload the image and a thumbnail will appear. Click _Remove_ if you want to remove the image. Add alternative text to the image. Uploading background image and adding alternate text to it is required.

5. _Save_ or add another paragraph or paragraph element.

![Grid item: Card](@guide_path/Assets/3_card.jpg)

##### Text

To add a Text element to the Grid paragraph item:

1. Click on _Add Text_ under the Grid elements list.

2. __Text__: Add and format your copy here. You can choose different text formats ( style, Basic HTML, Restricted HTML, Full HTML, GitHub Flavored Markdown) from the drop-down menu.

3. _Save_ or add another paragraph or paragraph element.

##### Image

To add an Image element to the Grid paragraph item:

1. Click on _Add Image_ under the Grid elements list.

2. __Image__: Upload an image. Click _Choose file_, locate the image on your computer, the system will upload the image and a thumbnail will appear. Click _Remove_ if you want to remove the image. Add alternative text to the image.

3. _Save_ or add another paragraph or paragraph element.

##### Benefit

To add a Benefit element to the Grid paragraph item:

1. Click on _Add Benefit_ under the Grid elements list.

2. __Text__: Add and format your copy here. You can choose different text formats ( style, Basic HTML, Restricted HTML, Full HTML, GitHub Flavored Markdown) from the drop-down menu.

3. __Icon__: Upload an icon (only png or svg). Click _Choose file_, locate the image on your computer, the system will upload the image and a thumbnail will appear. Click _Remove_ if you want to remove the image. Add alternative text to the image.

4. _Save_ or add another paragraph or paragraph element.

#### CTA

CTA stands for call-to-action. To add a Grid paragraph type to your landing page:

1. Click the _CTA_ button under the landing page element list.

2. __Text__: Add and format your copy here. You can choose different text formats ( style, Basic HTML, Restricted HTML, Full HTML, GitHub Flavored Markdown) from the drop-down menu.

3. __Buttons: URL__:  The URL of the page that the button points to.

4. __Buttons: Link text__: The text that will be displayed on the button.

5. __Buttons: Select a style__: Select button style. Available styles: primary, secondary, default, inverse.

6. _Save_ or add another paragraph.

![CTA paragraph](@guide_path/Assets/4_CTA.jpg)

## Creating API reference

You can create API reference content items in two ways:

* __Uploading an API reference file__: choose this method when you have an API reference documentation source file that you can upload.

* __Fill in values manually__: choose this method when you don’t have an API reference documentation source file yet, but you want to expose some information about the API (e.g.: the API is in the design phase).

To create an API Reference content item that will be displayed on the user interface, follow these steps:

1. Navigate to _Content_ > _Add content_ > _API Reference_.

2. Choose upload mode by using the _Mode_ selector. You have two options:

      a. _Upload an API reference file_ – select this option if you want to upload an API reference file. See [Upload an API reference file](#uploading-an-api-reference-file) section for further instructions.

      b. _Fill in the values manually_ – select this option if you want to upload an API reference file later. See [Fill in the values manually](#filling-in-the-values-manually) section for further instructions.

### Uploading an API reference file

After you selected the _Upload an API reference file_ option, follow these steps:

1. Upload the _Source file_ by clicking on _Choose file_ and locating the source file on your computer. The source file is a Swagger/OpenAPI JSON/YAML/YML file. The system will validate the file and display the result:

    a. If the source file is not valid, it displays a meaningful error message that informs you about the nature of the error.

    b. If the source file is valid, it loads the source file. A _Remove_ button appears.

2. Check the _Published_ checkbox if you want to publish the API Reference. Published content items will be available on the portal for users with the right permission.

3. _Save_.

![Upload an API reference file](@guide_path/Assets/6_sourcefile.jpg)

### Filling in the values manually

After you selected the _Fill in the values manually_ option, follow these steps:

1. Add a _Title_ to the API reference content item.

2. Add a description about the API to the _Description_ field.

3. Add a version number to the _Version_ field.

4. Check the _Published_ checkbox if you want to publish the API reference content item. Published content items will be available on the portal for users with the right permission.

5. _Save_.

![Fill in the values manually](@guide_path/Assets/7_apireference.jpg)

Now you created an API Reference content item that works as a placeholder as there is no source file attached. When you upload a source file to this API Reference content item, the title, the description, and the version number in the source file you upload will override the data that you provided manually. __After there is a source file uploaded, you are no longer able to provide data manually.__

## Creating an API bundle page

You can publish information about the API services that the  Developer Portal offers through the API bundle pages. The summary of the API bundle will appear on the Service page as an item of the service list.

You can create an API bundle page by following the steps below:

1. In the administrative menu, navigate to _Content_ > _Add content_ > _API bundle_.

2. Add a _Title_ to the API bundle.

3. Choose the appropriate _Domain_ to the API bundle. The API bundle you about to create will associated with the domain you choose here.

4. Assign the appropriate API reference(s) to the API bundle. The API reference(s) you choose here will be associated with the API bundle. You can add more API reference to the API bundle by clicking on _Add another item_.

5. Add a _Summary_ to the API bundle. The copy you provide here will appear on the _Services_ page where the services are listed.

    ![API bundle page form – first part](@guide_path/Assets/8_apibundle1.jpg)

6. Add your copy to the _Text_ field and edit it. The text you provide here will be displayed in the header of the API bundle page.

7. Upload a _Background image_. Click _Choose file_, locate the image on your computer, the system will upload the image and a thumbnail will appear. Click _Remove_ if you want to remove the image. Add alternative text to the image. The image will be displayed in the header of the API bundle page.

8. Add _Landing page elements_ to the API bundle page. See the [Paragraph Types](#paragraph-types) section for the available paragraph types and more details.

9. Check the _Published_ checkbox if you want to publish the page after saving. Leave it unchecked if you don't want to publish yet.

10. _Save_.

    ![API bundle page form – second part](@guide_path/Assets/9_apibundle2.jpg)

## Creating an API documentation page

 developer portal uses the Drupal Book module to manage API documentation pages and bundle together pages that related to each other.

To create an API documentation page, follow the steps below.

1. In the administrative menu, go to _Content_ > _Add content_ > _Book page_.

2. Add a _Title_ to the documentation page.

3. Add the piece of documentation to the _Body_ field. You can choose different text formats ( style, Basic HTML, Restricted HTML, Full HTML, GitHub Flavored Markdown) from the drop-down menu.

4. Check the _Published_ checkbox if you want to publish the page after saving. Leave it unchecked if you don't want to publish yet.

5. Add the documentation page to a book in the _Book outline_ section of the content settings menu on the right side of the page. Use the drop-down menu under the _Book_ section to select the book you want to assign the documentation page to. You can add the documentation page to a parent item of the book (subsection) by using the _Parent item_ selector. Choose the _Create a new book_ option if you want to add the page to a new book.

6. _Save_.

![Creating an API documentation page](@guide_path/Assets/10_apidoc.jpg)

__!NOTE__: The API documentation page you created will appear in the selected book.

![API documentation page in a book](@guide_path/Assets/11_example.jpg)

## Handling header background

 developer portal provides the possibility to display the same header background image on multiple pages without the need to upload the same file repeatedly. You can define a URL pattern and assign it to a header background image. The image will be displayed all the pages that match the URL pattern you defined.

### Uploading header background and defining URL pattern

To upload a header background and define a URL pattern, follow the steps below:

1. In the administrative menu, go to _Structure_ > _Header Background Settings_. The _Header Background List_ page opens, you can check, edit, or delete the existing URL definitions and background images in the list.

2. Click _Add Header Background_ at the top of the page.

3. Upload a _Background image_. Click _Choose file_, locate the image on your computer, the system will upload the image and a thumbnail will appear. Click _Remove_ if you want to remove the image. Add alternative text to the image.

4. Define a URL pattern in the _URL pattern_ field. You don't have to add the site domain name. You can use asterisk (\*) as a wildcard character which stands for any string of characters. For example if you want to use the uploaded image on all the pages that are under /services (e.g.: /services/api1, /services/api2, etc.), then define /services/* as a URL pattern. The wildcard character will stand for any path segment that follows /services.

5. _Save_.

## User handling

Regarding user handling, the  developer portal does not have any custom feature compared to the default user management in Drupal 8.

See the Drupal 8 user guide about [user account management](https://www.drupal.org/docs/guides/en/user-chapter.html).
