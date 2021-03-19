<?php

namespace App\Traits;

trait UploadFileTrait {
    function square_thumbnail_with_proportion($src_file,$destination_file,$square_dimensions=140, $extension, $jpeg_quality=90)
    {
        // Step one: Rezise with proportion the src_file *** I found this in many places.

        if (strtolower($extension) == 'jpeg' || strtolower($extension) == 'jpg')
            $src_img=imagecreatefromjpeg($src_file);
        elseif (strtolower($extension) == 'png')
            $src_img=imagecreatefrompng($src_file);

        $old_x=imageSX($src_img);
        $old_y=imageSY($src_img);

        $ratio1=$old_x/$square_dimensions;
        $ratio2=$old_y/$square_dimensions;

        if($ratio1>$ratio2)
        {
            $thumb_w=$square_dimensions;
            $thumb_h=$old_y/$ratio1;
        }
        else
        {
            $thumb_h=$square_dimensions;
            $thumb_w=$old_x/$ratio2;
        }

        // we create a new image with the new dimmensions
        $smaller_image_with_proportions=ImageCreateTrueColor($thumb_w,$thumb_h);

        // resize the big image to the new created one
        imagecopyresampled($smaller_image_with_proportions,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y);

        // *** End of Step one ***

        // Step Two (this is new): "Copy and Paste" the $smaller_image_with_proportions in the center of a white image of the desired square dimensions

        // Create image of $square_dimensions x $square_dimensions in white color (white background)
        $final_image = imagecreatetruecolor($square_dimensions, $square_dimensions);
        $bg = imagecolorallocate ( $final_image, 255, 255, 255 );
        imagefilledrectangle($final_image,0,0,$square_dimensions,$square_dimensions,$bg);

        // need to center the small image in the squared new white image
        if($thumb_w>$thumb_h)
        {
            // more width than height we have to center height
            $dst_x=0;
            $dst_y=($square_dimensions-$thumb_h)/2;
        }
        elseif($thumb_h>$thumb_w)
        {
            // more height than width we have to center width
            $dst_x=($square_dimensions-$thumb_w)/2;
            $dst_y=0;

        }
        else
        {
            $dst_x=0;
            $dst_y=0;
        }

        $src_x=0; // we copy the src image complete
        $src_y=0; // we copy the src image complete

        $src_w=$thumb_w; // we copy the src image complete
        $src_h=$thumb_h; // we copy the src image complete

        $pct=100; // 100% over the white color ... here you can use transparency. 100 is no transparency.

        imagecopymerge($final_image,$smaller_image_with_proportions,$dst_x,$dst_y,$src_x,$src_y,$src_w,$src_h,$pct);

        if (strtolower($extension) == 'jpeg' || strtolower($extension) == 'jpg')
            imagejpeg($final_image,$destination_file,$jpeg_quality);
        elseif (strtolower($extension) == 'png')
            imagepng($final_image,$destination_file,9);

        // destroy aux images (free memory)
        imagedestroy($src_img);
        imagedestroy($smaller_image_with_proportions);
        imagedestroy($final_image);
    }

    function articleimage($article, $request, $image_flag=""){
        $path = "articleimage";
        $articlePath = public_path($path).'/'.$article->id;

        if ($request->file('image') != '' && $image_flag=="") {
            $file =  $request->file('image');
            $filenameWithExt = $file->getClientOriginalName();
            $extention = explode('/',$file->getClientMimeType());

            $imageName = $article->id . '_' . time() . '.' . $filenameWithExt;
            $mainImage = public_path($path).'/'.$article->id.'/'.$imageName;
            $thumbPath = public_path($path).'/'.$article->id.'/'.'thumb_'.$imageName;
            
            $file->move($articlePath, $imageName);
            $this->square_thumbnail_with_proportion($mainImage, $thumbPath, env('PROJECT_THUMBNAIL_WIDTH', 110), $extention[1]);
            
            return $imageName;
        }
    }
}