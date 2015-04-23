# Mouf Imagine
Mouf Imagine uses the [imagine/imagine](https://github.com/avalanche123/Imagine) library to create automated image transformations based on images' paths.

> A big thank to [Romain Neutron](https://github.com/romainneutron) for his great work, the quality of this library allowed us to integrate it in our framework with a few lines of code.

## The concept

| Original image | Transformed image |
|----------------|:-----------------:|
| Let's say someone uploaded this image :
   [original image](http://kzjbzekjfbzfkzjefzkfhzeffzjekz) | But you need to display it in a smaller size (300px width), and in grayscale :
   [transformed image](http://lhzrlzrlzelzljfhdzlbjzk)|
| Your original image's URL is [WEB_ROOT]/uploads/image.jpg | Then, you just have to enter this URL for generating / displaying the transformed image :  [WEB_ROOT]/[path_to_transformed_folder]/image.jpg |

