# Adding guides
1. Create a folder where you use underscores instead of spaces, e.g. `User_guide`, this will be the link appearing on `/admin/guides`
2. Create a markdown file inside the guide folder, e.g. `user-guide.md`, this will be the url for the guide: `admin/guides/user-guide`
3. You can add images and asstes by creating an `Assets` folder inside the guide's folder, then use the `@guide_path` token for defining their location, where `@guide_path` is the folder of the guide, e.g. `![Landing page editing](@guide_path/Assets/2_editing.jpg)` 

## Notes
* Only folders containing a single markdown file will work.
* Make sure the file extension `.md` is lowercase.