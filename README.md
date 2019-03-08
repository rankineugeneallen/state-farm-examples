# state-farm-examples
Code examples for State Farm 

## copyInfo.js
```
This compares data between two sections on a page. In this example, it compares data between the registration form and the billing form. There is a button that will copy data from the registration to billing. When the user hovers over the button, the fields on the registration form that will be copied are highlighted orange. If the user has already filled in fields on billing, the fields where the data does not match is highlighted (assuming there is a difference in data, if there is none, it will not highlight). When the copy button is clicked, the fields with differing data will be overwritten from the registration side to the billing side, and those fields that changed will flash orange.
```


## resize.js
```
Watches the size of the page and will change button values accordingly and make a form stick to top of page when scrolling if window dimensions are good. If the window size is too short, where the billing field can't be viewed without scrolling, then it will not stick. It will also not stick if the window width is too small, in which case it is classified as a mobile device. In the event that window size matches mobile, then the copy button's text will change from "Copy Info from Registrant" to "Copy to Billing". 
```

## database.php
```
A collection of SQL calls for SQL Server. Depending on what $Selection is set to, will get relevent information from the DB. Will print out error if query fails or not all necessary values are set. 
```
