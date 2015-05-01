# Mouf Imagine

Mouf Imagine uses the [imagine/imagine](https://github.com/avalanche123/Imagine) library to create automated image transformations based on images' paths.

> A big thank to [Romain Neutron](https://github.com/romainneutron) for his great work, the quality of this library allowed us to integrate it in our framework with a few lines of code.

## The concept

<table style="width 100%; text-align: center">
<tr>
<th colspan="3">Let's say someone uploaded this image, But you need to display it in a smaller size, and in grayscale</th>
</tr>
<tr>
<td>
<img  width="300px"  src="https://raw.githubusercontent.com/thecodingmachine/utils.graphics.mouf-imagine/1.0/doc/img/imagine.jpg" />
</td>
<td style="width: 20px;"> ==> </td>
<td>
<img width="200px" src="https://raw.githubusercontent.com/thecodingmachine/utils.graphics.mouf-imagine/1.0/doc/img/imagine-preset.jpg" />
</td>
</tr>
<tr>
<td> [WEB_ROOT]/uploads/image.jpg </td>
<td></td>
<td> [WEB_ROOT]/300x300_black_n_white/image.jpg </td>
</tr>
</table>

## How to ?

This is very simple ! All you have to do is create an instance of the `ImagePresetController` class :

<img width="80%" src="https://raw.githubusercontent.com/thecodingmachine/utils.graphics.mouf-imagine/1.0/doc/img/simple-instannce.png" />

In fact, what this package does for you is : apply a set of imagine filters on the original image, and display the image after saving it in the desired folder. All the rest is done by Imaging filters.





